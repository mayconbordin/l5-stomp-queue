<?php

use Mayconbordin\L5StompQueue\StompQueue;
use Mockery as m;

class StompQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mockery\MockInterface
     */
    protected $stomp;

    /**
     * @var StompQueue
     */
    protected $queue;

    protected function setUp()
    {
        $this->stomp = m::mock('Stomp\StatefulStomp');
        $this->stomp->shouldReceive('disconnect');

        $this->queue = new StompQueue($this->stomp, 'test');

        $container = m::mock('Illuminate\Container\Container');
        $this->queue->setContainer($container);
    }

    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }


    public function testPush()
    {
        $job   = 'job';
        $data  = 'data';
        $queue = 'test';

        $expected = json_encode(['job' => $job, 'data' => $data]);

        $this->stomp->shouldReceive('send')->once()->with($queue, $expected, []);
        $this->queue->push($job, $data);
    }

    public function testPushRaw()
    {
        $data  = 'data';
        $queue = 'test';
        $headers = ['delay' => 10];

        $this->stomp->shouldReceive('send')->once()->with($queue, $data, []);
        $this->queue->pushRaw($data, $queue);

        $this->stomp->shouldReceive('send')->once()->with($queue, $data, $headers);
        $this->queue->pushRaw($data, $queue, $headers);
    }

    public function testRecreate()
    {
        $data  = 'data';
        $queue = 'test';

        $this->stomp->shouldReceive('send')->once()->with($queue, $data, []);
        $this->queue->recreate($data, $queue, 0);
    }

    public function testLater()
    {
        $job   = 'job';
        $data  = 'data';
        $queue = 'test';

        $expected = json_encode(['job' => $job, 'data' => $data]);

        $this->stomp->shouldReceive('send')->once()->with($queue, $expected, []);
        $this->queue->later(10, $job, $data, $queue);
    }

    public function testPop()
    {
        $queue = 'test';
        $body = ['job' => 'job-1', 'queue' => $queue, 'attempts' => 1];
        $message = new FuseSource\Stomp\Frame(null, null, json_encode($body));

        $this->stomp->shouldReceive('subscribe')->once()->with($queue);
        $this->stomp->shouldReceive('readFrame')->once()->andReturn($message);

        $job = $this->queue->pop($queue);

        $this->assertEquals($body['job'], $job->getName());
        $this->assertEquals($body['queue'], $job->getQueue());
        $this->assertEquals(json_encode($body), $job->getRawBody());
    }

    public function testDeleteMessage()
    {
        $body = ['job' => 'job-1', 'queue' => 'test', 'attempts' => 1];
        $message = new FuseSource\Stomp\Frame(null, null, json_encode($body));

        $this->stomp->shouldReceive('ack')->once()->with($message);

        $this->queue->deleteMessage('test', $message);
    }

}