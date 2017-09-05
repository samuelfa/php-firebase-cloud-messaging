<?php
namespace sngrl\PhpFirebaseCloudMessaging;

use Psr\Http\Message\ResponseInterface;

/**
 * @author sngrl
 */
class Client implements ClientInterface
{
    const DEFAULT_API_URL = 'https://fcm.googleapis.com/fcm/send';
    const DEFAULT_TOPIC_ADD_SUBSCRIPTION_API_URL = 'https://iid.googleapis.com/iid/v1:batchAdd';
    const DEFAULT_TOPIC_REMOVE_SUBSCRIPTION_API_URL = 'https://iid.googleapis.com/iid/v1:batchRemove';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $proxyApiUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * Client constructor.
     * @param \GuzzleHttp\Client $guzzleClient
     */
    public function __construct(\GuzzleHttp\Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * add your server api key here
     * read how to obtain an api key here: https://firebase.google.com/docs/server/setup#prerequisites
     *
     * @param string $apiKey
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Client
     */
    public function setApiKey(string $apiKey) : self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * people can overwrite the api url with a proxy server url of their own
     *
     * @param string $url
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Client
     */
    public function setProxyApiUrl(string $url) : self
    {
        $this->proxyApiUrl = $url;
        return $this;
    }

    /**
     * sends your notification to the google servers and returns a guzzle response object
     * containing their answer.
     *
     * @param Message $message
     * @return ResponseInterface
     * @throws \Exception
     */
    public function send(Message $message) : ResponseInterface
    {
        if(empty($this->apiKey)){
            throw new \Exception('Should be configure API key value previously to send a push message');
        }

        return $this->guzzleClient->post(
            $this->getApiUrl(),
            [
                'headers' => [
                    'Authorization' => sprintf('key=%s', $this->apiKey),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($message)
            ]
        );
    }

    /**
     * @param integer $topicId
     * @param array|string $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addTopicSubscription(int $topicId, $recipientsTokens) : ResponseInterface
    {
        return $this->processTopicSubscription($topicId, $recipientsTokens, self::DEFAULT_TOPIC_ADD_SUBSCRIPTION_API_URL);
    }


    /**
     * @param integer $topicId
     * @param array|string $recipientsTokens
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function removeTopicSubscription(int $topicId, $recipientsTokens) : ResponseInterface
    {
        return $this->processTopicSubscription($topicId, $recipientsTokens, self::DEFAULT_TOPIC_REMOVE_SUBSCRIPTION_API_URL);
    }


    /**
     * @param integer $topicId
     * @param array|string $recipientsTokens
     * @param string $url
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function processTopicSubscription(int $topicId, $recipientsTokens, string $url) : ResponseInterface
    {
        if (!is_array($recipientsTokens))
            $recipientsTokens = [$recipientsTokens];

        return $this->guzzleClient->post(
            $url,
            [
                'headers' => [
                    'Authorization' => sprintf('key=%s', $this->apiKey),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode([
                    'to' => '/topics/' . $topicId,
                    'registration_tokens' => $recipientsTokens,
                ])
            ]
        );
    }

    /**
     * Return the url configure to use the API, by the is using the constant self::DEFAULT_API_URL
     * @return string
     */
    private function getApiUrl() : string
    {
        return isset($this->proxyApiUrl) ? $this->proxyApiUrl : self::DEFAULT_API_URL;
    }
}
