<?php

namespace PedroSancao\Wpsg;

use PedroSancao\Wpsg\Exceptions\ApiException;

class ApiClient
{
    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Create new instance
     *
     * @param string $wordpressUrl
     */
    public function __construct(string $wordpressUrl)
    {
        $this->baseUrl = rtrim($wordpressUrl, '/') . '/wp-json/wp/v2/';
    }

    /**
     * Load data from Wordpress REST API
     *
     * @param string $endpoint
     * @param array $parameters
     * @return array|object
     * @throws PedroSancao\Wpsg\Exceptions\ApiException
     */
    public function loadData(string $endpoint, array $parameters = [])
    {
        if (false === $data = file_get_contents($this->baseUrl . $endpoint . '?' . http_build_query($parameters))) {
            throw new ApiException("Request on {$this->baseUrl}{$endpoint} failed");
        }
        return json_decode($data);
    }
}
