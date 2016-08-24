<?php

class ConnectionTest extends PHPUnit_Framework_TestCase {

    public function testConnect()
    {
        // make a connection
        //$con = new \FuseSource\Stomp\Stomp("tcp://10.200.116.202:61613");
        $con = new \Stomp\StatefulStomp(new \Stomp\Client("tcp://localhost:61613"));

        $con->connect();

        $this->assertTrue($con->isConnected());
        $this->assertNotNull($con->getSessionId());

        $con->send("test", "hello");

        $con->disconnect();
    }

    /*public function testConsume()
    {
        // make a connection
        $con = new \FuseSource\Stomp\Stomp("tcp://localhost:61613");

        $con->connect();

        $this->assertTrue($con->isConnected());
        $this->assertNotNull($con->getSessionId());

        $con->subscribe("teste");

        $msg = $con->readFrame();

        if ( $msg != null) {
            echo "Received message with body '$msg->body'\n";
            // mark the message as received in the queue
            $con->ack($msg);
        } else {
            echo "Failed to receive a message\n";
        }

        $con->disconnect();
    }*/
}