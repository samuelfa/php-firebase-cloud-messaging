<?php
namespace sngrl\PhpFirebaseCloudMessaging\Recipient;

/**
 * Class Topic
 * @package sngrl\PhpFirebaseCloudMessaging\Recipient
 */
class Topic extends Recipient
{
    /**
     * @var string
     */
    private $name;

    /**
     * Topic constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }
}
