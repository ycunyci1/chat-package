<?php

namespace Dd1\Chat\Services;

class CentrifugoService
{
    protected $client;

    public function __construct()
    {
        $this->client = new \phpcent\Client(
            config('centrifugo.url') . '/api',
            config('centrifugo.api_key'),
            config('centrifugo.secret'),
        );
    }

    public function publishToChannel($channel, $data)
    {
        $this->client->publish($channel, $data);
    }
}
