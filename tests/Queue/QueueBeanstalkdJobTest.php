<?php

use Mockery as m;
use Illuminate\Queue\Connectors\BeanstalkdConnector;

class QueueBeanstalkdJobTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testFireProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getJob()->shouldReceive('getData')->once()->andReturn(json_encode(['job' => 'foo', 'data' => ['data']]));
        $job->getContainer()->shouldReceive('make')->once()->with('foo')->andReturn($handler = m::mock('StdClass'));
        $handler->shouldReceive('fire')->once()->with($job, ['data']);

        $job->fire();
    }

    public function testFailedProperlyCallsTheJobHandler()
    {
        $job = $this->getJob();
        $job->getJob()->shouldReceive('getData')->once()->andReturn(json_encode(['job' => 'foo', 'data' => ['data']]));
        $job->getContainer()->shouldReceive('make')->once()->with('foo')->andReturn($handler = m::mock('BeanstalkdJobTestFailedTest'));
        $handler->shouldReceive('failed')->once()->with(['data']);

        $job->failed();
    }

    public function testDeleteRemovesTheJobFromBeanstalkd()
    {
        $job = $this->getJob();
        $job->getJob()->shouldReceive('delete')->once()->andReturn(true);

        $job->delete();
    }

    public function testReleaseProperlyReleasesJobOntoBeanstalkd()
    {
        $job = $this->getJob();
        $job->getJob()->shouldReceive('release')->once()->with(0, BeanstalkdConnector::DEFAULT_PRIORITY);

        $job->release();
    }

    public function testBuryProperlyBuryTheJobFromBeanstalkd()
    {
        $job = $this->getJob();
        $job->getJob()->shouldReceive('bury')->once();

        $job->bury();
    }

    protected function getJob()
    {
        return new Illuminate\Queue\Jobs\BeanstalkdJob(
            m::mock('Illuminate\Container\Container'),
            m::mock('Beanstalk\Pool'),
            m::mock('Beanstalk\Job'),
            'default'
        );
    }
}

class BeanstalkdJobTestFailedTest
{
    public function failed(array $data)
    {
        //
    }
}
