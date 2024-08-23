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

namespace Nahuomall\HyperfTenancy\Kernel\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

trait Guzzle
{
    public array $defaultConfig = [];

    public array $userConfig = [];

    private Client $client;

    /**
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function post(string $uri, array $data): ResponseInterface
    {
        return $this->client->post($uri, $this->mergeData($data));
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function get(string $uri, array $data): ResponseInterface
    {
        return $this->client->get($uri, $this->mergeData($data));
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function put(string $uri, array $data): ResponseInterface
    {
        return $this->client->put($uri, $this->mergeData($data));
    }

    /**
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function delete(string $uri, array $data): ResponseInterface
    {
        return $this->client->delete($uri, $this->mergeData($data));
    }

    /**
     * @param array $data
     * @return array
     */
    private function mergeData(array $data): array
    {
        return array_replace_recursive($this->defaultConfig, $this->userConfig, $data);
    }
}