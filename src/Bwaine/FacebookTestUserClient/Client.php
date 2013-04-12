<?php

namespace Bwaine\FacebookTestUserClient;

use Guzzle\Common\Collection;
use Guzzle\Service\Client as GuzzleClient;
use Guzzle\Service\Description\ServiceDescription;

/**
 * My example web service client
 */
class Client extends GuzzleClient {

    /**
     * Factory method to create a new MyServiceClient
     *
     * The following array keys and values are available options:
     * - base_url: Base URL of web service
     * - username: API username
     * - password: API password
     *
     * @param array|Collection $config Configuration data
     *
     * @return self
     */
    public static function factory($config = array()) {
        $default = array(
            'base_url' => 'https://graph.facebook.com'
        );

        $required = array();

        $config = Collection::fromConfig($config, $default, $required);

        $client = new self($config->get('base_url'), $config);
        // Attach a service description to the client
        $description = ServiceDescription::factory(__DIR__ . '/description.json');
        $client->setDescription($description);

        return $client;
    }

}