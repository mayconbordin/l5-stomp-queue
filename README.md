l5-stomp-queue
==============

Stomp Queue Driver for Laravel 5.

## Installation

In order to install l5-stomp-queue, just add

```json
"mayconbordin/l5-stomp-queue": "dev-master"
```
	
to your composer.json. Then run `composer install` or `composer update`.

Add the Service Provider to the `providers` array in `config/app.php`:
	
```php
'providers' => array(
    ...
    'Mayconbordin\L5StompQueue\StompServiceProvider',
)
```

And add the driver configuration to the `connections` array in `config/queue.php`:

```php
'connections' => array(
    'stomp' => [
        'driver'     => 'stomp',
        'broker_url' => 'tcp://localhost:61613',
        'queue'      => 'default',
        'system'     => 'activemq'
    ]
)
```


## Configuration Options

### `queue`

The name of the queue.

### `system`

The name of the system that implements the Stomp protocol. Default: `null`.

This value is used for setting custom headers (not defined in the protocol). In the case of ActiveMQ, it will set the 
`AMQ_SCHEDULED_DELAY` (see [docs](http://activemq.apache.org/nms/stomp-delayed-and-scheduled-message-feature.html))
header in order to give support for the `later` method, defined at `Illuminate\Contracts\Queue`.
 
### `sync`
 
Whether the driver should be synchronous or not when sending messages. Default: `false`.
 
### `prefetchSize`
 
The number of messages that will be streamed to the consumer at any point in time. Applicable only to ActiveMQ. Default: `1`.
 
For more information see the [ActiveMQ documentation](http://activemq.apache.org/what-is-the-prefetch-limit-for.html).
 
### `clientId`
 
Used for durable topic subscriptions. It will set the `activemq.subcriptionName` property. See [documentation](http://activemq.apache.org/stomp.html#Stomp-ActiveMQextensionstoStomp)
for more information.