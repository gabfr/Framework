<?php

namespace Illuminate\Queue\Connectors;

use Illuminate\Support\Arr;
use Illuminate\Queue\BeanstalkdQueue;
use Beanstalk\Pool;

class BeanstalkdConnector implements ConnectorInterface
{
    const DEFAULT_PORT = 11300;
    const DEFAULT_DELAY = 0; // no delay
    const DEFAULT_PRIORITY = 1024; // most urgent: 0, least urgent: 4294967295
    const DEFAULT_TTR = 60; // 1 minute
    const DEFAULT_TUBE = 'default';

    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $beanstalkPool = new Pool();
        
        $servers = Arr::get($config, 'servers', []);

        if (count($servers) > 0) {
            foreach ($servers as $serverConfig) 
                $beanstalkPool->addServer($serverConfig['host'], Arr::get($config, 'port', BeanstalkdConnector::DEFAULT_PORT));
        } else {
            $beanstalkPool->addServer($config['host'], Arr::get($config, 'port', BeanstalkdConnector::DEFAULT_PORT));
        }

        return new BeanstalkdQueue(
            $beanstalkPool, $config['queue'], Arr::get($config, 'ttr', BeanstalkdConnector::DEFAULT_TTR)
        );
    }
}
