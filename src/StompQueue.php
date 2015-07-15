<?php namespace Mayconbordin\L5StompQueue;

use FuseSource\Stomp\Frame;
use FuseSource\Stomp\Stomp;
use Illuminate\Queue\Queue;
use Illuminate\Contracts\Queue\Queue as QueueContract;
use Mayconbordin\L5StompQueue\Jobs\StompJob;

/**
 * Class StompQueue
 * @package Mayconbordin\L5StompQueue
 * @author Maycon Viana Bordin <mayconbordin@gmail.com>
 */
class StompQueue extends Queue implements QueueContract
{
    const SYSTEM_ACTIVEMQ = "activemq";

    /**
     * The Stomp instance.
     *
     * @var Stomp
     */
    protected $stomp;

    /**
     * The name of the default queue.
     *
     * @var string
     */
    protected $default;

    /**
     * The system name.
     *
     * @var string
     */
    protected $system;

    /**
     * Create a new ActiveMQ queue instance.
     *
     * @param Stomp $stomp
     * @param string $default
     * @param string|null $system
     */
    public function __construct(Stomp $stomp, $default, $system = null)
    {
        $this->stomp   = $stomp;
        $this->default = $default;
        $this->system  = $system;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushRaw($this->createPayload($job, $data), $queue);
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string $payload
     * @param  string $queue
     * @param  array $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $this->stomp->send($this->getQueue($queue), $payload, $options);
    }

    /**
     * Push a raw payload onto the queue after encrypting the payload.
     *
     * @param  string  $payload
     * @param  string  $queue
     * @param  int     $delay
     * @return mixed
     */
    public function recreate($payload, $queue = null, $delay)
    {
        return $this->pushRaw($payload, $queue, $this->makeDelayHeader($delay));
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTime|int $delay
     * @param  string $job
     * @param  mixed $data
     * @param  string $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        $payload = $this->createPayload($job, $data, $queue);
        return $this->pushRaw($payload, $queue, $this->makeDelayHeader($delay));
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string $queue
     * @return StompJob|null
     */
    public function pop($queue = null)
    {
        $this->stomp->subscribe($this->getQueue($queue));
        $job = $this->stomp->readFrame();

        if (!is_null($job)) {
            return new StompJob($this->container, $this, $job);
        }
    }

    /**
     * Delete a message from the Stomp queue.
     *
     * @param  string  $queue
     * @param  string|Frame $message
     * @return void
     */
    public function deleteMessage($queue, $message)
    {
        $this->stomp->ack($message);
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * @param int $delay
     * @return array
     */
    protected function makeDelayHeader($delay)
    {
        $delay = $this->getSeconds($delay);

        if ($this->system == self::SYSTEM_ACTIVEMQ) {
            return ['AMQ_SCHEDULED_DELAY' => $delay * 1000];
        } else {
            return [];
        }
    }
}