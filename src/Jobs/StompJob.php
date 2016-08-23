<?php 

namespace Nfinzer\L5StompQueue\Jobs;

use Stomp\Transport\Frame;
use Nfinzer\L5StompQueue\StompQueue;
use Illuminate\Support\Arr;
use Illuminate\Container\Container;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Contracts\Queue\Job as JobContract;

/**
 * Class StompJob
 * @package Nfinzer\L5StompQueue\Jobs
 * @author Maycon Viana Bordin <mayconbordin@gmail.com>
 */
class StompJob extends Job implements JobContract
{
    /**
     * The Stomp instance.
     *
     * @var StompQueue
     */
    protected $stomp;

    /**
     * The Stomp message instance.
     *
     * @var Frame
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param Container $container
     * @param StompQueue $stomp
     * @param Frame $job
     */
    public function __construct(Container $container, StompQueue $stomp, Frame $job)
    {
        $this->job = $job;
        $this->stomp = $stomp;
        $this->container = $container;
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
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $this->stomp->deleteMessage($this->getQueue(), $this->job);
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);
        $this->recreateJob($delay);
    }

    /**
     * Release a pushed job back onto the queue.
     *
     * @param  int  $delay
     * @return void
     */
    protected function recreateJob($delay)
    {
        $payload = json_decode($this->job->body, true);
        Arr::set($payload, 'attempts', Arr::get($payload, 'attempts', 1) + 1);

        $this->stomp->recreate(json_encode($payload), $this->getQueue(), $delay);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        return Arr::get(json_decode($this->job->body, true), 'attempts', 1);
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return Arr::get(json_decode($this->job->body, true), 'job');
    }

    /**
     * Get the name of the queue the job belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return Arr::get(json_decode($this->job->body, true), 'queue');
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        return $this->job->body;
    }
}