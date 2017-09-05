<?php
namespace sngrl\PhpFirebaseCloudMessaging;

/**
 *
 * @author sngrl
 *
 */
interface ClientInterface
{

    /**
     * add your server api key here
     * read how to obtain an api key here: https://firebase.google.com/docs/server/setup#prerequisites
     *
     * @param string $apiKey
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Client
     */
    public function setApiKey(string $apiKey);
    

    /**
     * people can overwrite the api url with a proxy server url of their own
     *
     * @param string $url
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Client
     */
    public function setProxyApiUrl(string $url);

    /**
     * sends your notification to the google servers and returns a guzzle response object
     * containing their answer.
     *
     * @param Message $message
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function send(Message $message);
    
}
