<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Nahuomall\HyperfTenancy\Commands;

use Hyperf\CodeParser\Project;
use Hyperf\Database\Commands\Ast\ModelUpdateVisitor;
use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\Commands\ModelData;
use Hyperf\Database\Commands\ModelOption;
use Hyperf\Database\Model\Model;
use Hyperf\Stringable\Str;
use Nahuomall\HyperfTenancy\Concerns\HasATenantsOption;
use Nahuomall\HyperfTenancy\Kernel\Exceptions\TenancyException;
use Nahuomall\HyperfTenancy\Kernel\Tenancy;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ModelMigration extends ModelCommand
{
    use HasATenantsOption;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct($container);
        $this->setDescription('Create new model classes by tenant.');
        parent::setName('tenants:model');
    }

    /**
     * @throws TenancyException
     */
    public function handle(): void
    {
        Tenancy::runForMultiple(Tenancy::baseDatabase(), function ($tenant) {
            $this->line("Tenant: {$tenant['id']}");
            if (empty($this->input->getOption('inheritance'))) {
                $this->input->setOption('inheritance', '\\' . Model::class);
            }
            $this->input->setOption('pool', Tenancy::initDbConnectionName($tenant['id']));
            $this->input->setOption('with-comments', true);
            parent::handle();
        });
    }

    /**
     * Configure the command options.
     */
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass(string $table, string $name, ModelOption $option): string
    {
        $stub = file_get_contents(__DIR__ . '/stubs/Model.stub');

        return $this->replaceNamespace($stub, $name)
            ->replaceInheritance($stub, $option->getInheritance())
            ->replaceUses($stub, $option->getUses())
            ->replaceClass($stub, $name)
            ->replaceTable($stub, $table);
    }

    /**
     * @param string $table
     * @param ModelOption $option
     * @return void
     */
    protected function createModel(string $table, ModelOption $option): void
    {
        $builder = $this->getSchemaBuilder($option->getPool());
        $table = Str::replaceFirst($option->getPrefix(), '', $table);
        $columns = $this->formatColumns($builder->getColumnTypeListing($table));
        $project = new Project();
        $class = $option->getTableMapping()[$table] ?? Str::studly(Str::singular($table));
        $class = $project->namespace($option->getPath()) . $class;
        $path = BASE_PATH . '/' . $project->path($class);
        if (! file_exists($path)) {
            $this->mkdir($path);
            file_put_contents($path, $this->buildClass($table, $class, $option));
        }
        $columns = $this->getColumns($class, $columns, $option->isForceCasts());
        $traverser = new NodeTraverser();
        $traverser->addVisitor(make(ModelUpdateVisitor::class, [
            'class' => $class,
            'columns' => $columns,
            'option' => $option,
        ]));
        $data = make(ModelData::class, ['class' => $class, 'columns' => $columns]);
        foreach ($option->getVisitors() as $visitorClass) {
            $traverser->addVisitor(make($visitorClass, [$option, $data]));
        }
        $traverser->addVisitor(new CloningVisitor());

        $originStmts = $this->astParser->parse(file_get_contents($path));
        $originTokens = $this->lexer->getTokens();
        $newStmts = $traverser->traverse($originStmts);
        $code = $this->printer->printFormatPreserving($newStmts, $originStmts, $originTokens);
        file_put_contents($path, $code);
        $this->output->writeln(sprintf('<info>Model %s was created.</info>', $class));

        if ($option->isWithIde()) {
            $this->generateIDE($code, $option, $data);
        }
    }
}
