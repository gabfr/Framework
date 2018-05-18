<?php

namespace Illuminate\Queue\Jobs;

use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job as JobContract;
use Beanstalk\Job as BeanstalkJob;
use Beanstalk\Pool as BeanstalkPool;
use Illuminate\Queue\Connectors\BeanstalkdConnector;

class BeanstalkdJob extends Job implements JobContract
{
    /**
     * The pHp-Beanstalk instance.
     *
     * @var \Beanstalk\Pool
     */
    protected $pool;

    /**
     * The Beanstalk job instance.
     *
     * @var \Beanstalk\Job
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  \Beanstalk\Pool  $pool
     * @param  \Beanstalk\Job  $job
     * @param  string  $queue
     * @return void
     */
    public function __construct(Container $container,
                                BeanstalkPool $pool,
                                BeanstalkJob $job,
                                $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->container = $container;
        $this->pool = $pool;
    }

    /**
     * Fire the job.
     *
     * @return void
     */
    public function fire()
    {
        $this->resolveAndFire(json_decode($this->getRawBody(), true));
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->getData();
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        return $this->job->delete();
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int   $delay
     * @return void
     */
    public function release($delay = 0, $priority = BeanstalkdConnector::DEFAULT_PRIORITY)
    {
        parent::release($delay);

        $this->job->release($delay, $priority);
    }

    /**
     * Bury the job in the queue.
     *
     * @return void
     */
    public function bury($priority = 2048)
    {
        parent::release();

        $this->job->bury();
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        $stats = $this->job->stats();
        return (int) $stats->getStat('reserves');
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->getId();
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Illuminate\Container\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the underlying Beanstalk\Pool instance.
     * 
     * @return \Beanstalk\Job
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * Get the underlying Beanstalk\Job instance.
     * 
     * @return \Beanstalk\Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Get the underlying Pheanstalk instance.
     * @DEPRECATED
     */
    public function getPheanstalk()
    {
        throw new DeprecatedException('Pheanstalk is not longer used');
    }

    /**
     * Get the underlying Pheanstalk job.
     * @DEPRECATED
     */
    public function getPheanstalkJob()
    {
        throw new DeprecatedException('Pheanstalk is not longer used anymore');
    }
}
