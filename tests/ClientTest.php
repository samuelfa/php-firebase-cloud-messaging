<?php
namespace sngrl\PhpFirebaseCloudMessaging\Tests;

use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Message;

use GuzzleHttp\Psr7\Response;

class ClientTest extends PhpFirebaseCloudMessagingTestCase
{
    /**
     * @var Client
     */
    private $fixture;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Client( new \GuzzleHttp\Client());
    }

    public function testSendConstruesValidJsonForNotificationWithTopic()
    {
        $apiKey = 'key';
        $headers = array(
            'Authorization' => sprintf('key=%s', $apiKey),
            'Content-Type' => 'application/json'
        );

        $guzzle = \Mockery::mock(\GuzzleHttp\Client::class);
        $guzzle->shouldReceive()
            ->once()
            ->with(Client::DEFAULT_API_URL, array('headers' => $headers, 'body' => '{"to":"\\/topics\\/test"}'))
            ->andReturn(\Mockery::mock(Response::class));

        $this->fixture->setApiKey($apiKey);

        $message = new Message();
        $message->addRecipient(new Topic('test'));

        $this->fixture->send($message);
    }
}