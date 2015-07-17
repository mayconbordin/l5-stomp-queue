<?php namespace Mayconbordin\L5StompQueue\Connectors;

use FuseSource\Stomp\Stomp;
use Illuminate\Support\Arr;
use Illuminate\Queue\Connectors\ConnectorInterface;
use Mayconbordin\L5StompQueue\StompQueue;

/**
 * Class StompConnector
 * @package Mayconbordin\L5StompQueue\Connectors
 * @author Maycon Viana Bordin <mayconbordin@gmail.com>
 */
class StompConnector implements ConnectorInterface
{
    /**
     * Establish a queue connection.
     *
     * @param  array $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $stomp = new Stomp($config['broker_url']);
        $stomp->sync         = Arr::get($config, 'sync', false);
        $stomp->prefetchSize = Arr::get($config, 'prefetchSize', 1);
        $stomp->clientId     = Arr::get($config, 'clientId', null);

        return new StompQueue($stomp, $config['queue'], Arr::get($config, 'system', null), [
            'username' => Arr::get($config, 'username', ''),
            'password' => Arr::get($config, 'password', '')
        ]);
    }
}