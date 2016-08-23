<?php 

namespace Nfinzer\L5StompQueue\Broadcasters;

use Stomp\StatefulStomp as Stomp;
use Illuminate\Contracts\Broadcasting\Broadcaster;
use Illuminate\Support\Arr;

class StompBroadcaster implements Broadcaster
{
    /**
     * The Stomp instance.
     *
     * @var Stomp
     */
    protected $stomp;

    /**
     * The Stomp credentials for connection.
     *
     * @var array
     */
    protected $credentials;

    /**
     * Create a Stomp Broadcaster.
     *
     * @param Stomp $stomp
     * @param array $credentials [username=string, password=string]
     */
    public function __construct(Stomp $stomp, array $credentials = [])
    {
        $this->stomp = $stomp;
        $this->credentials = $credentials;
    }

    /**
     * Broadcast the given event.
     *
     * @param  array $channels
     * @param  string $event
     * @param  array $payload
     * @return void
     */
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $this->connect();

        $payload = json_encode(['event' => $event, 'data' => $payload]);

        foreach ($channels as $channel) {
            $this->stomp->send($channel, $payload);
        }
    }

    /**
     * Connect to Stomp server, if not connected.
     *
     * @throws \FuseSource\Stomp\Exception\StompException
     */
    protected function connect()
    {
        if (!$this->stomp->isConnected()) {
            $this->stomp->connect(Arr::get($this->credentials, 'username', ''), Arr::get($this->credentials, 'password', ''));
        }
    }
}
