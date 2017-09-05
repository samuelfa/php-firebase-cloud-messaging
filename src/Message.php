<?php
namespace sngrl\PhpFirebaseCloudMessaging;

use sngrl\PhpFirebaseCloudMessaging\Recipient\Recipient;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;

/**
 * Class Message
 * @package sngrl\PhpFirebaseCloudMessaging
 * @author sngrl
 */
class Message implements \JsonSerializable
{
    /**
     * Maximum topics and devices: https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream
     */
    const MAX_TOPICS = 3;
    const MAX_DEVICES = 1000;

    /**
     * @var Notification
     */
    private $notification;

    /**
     * @var string
     */
    private $collapseKey;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var boolean
     */
    private $contentAvailable;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Device|Topic[]
     */
    private $recipients = [];

    /**
     * @var string
     */
    private $recipientType;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @var string
     */
    private $condition;

    /**
     * Specify a condition pattern when sending to combinations of topics
     * https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server
     *
     * Examples:
     * "%s && %s" > Send to devices subscribed to topic 1 and topic 2
     * "%s && (%s || %s)" > Send to devices subscribed to topic 1 and topic 2 or 3
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition(string $condition) : self
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * where should the message go
     *
     * @param Recipient $recipient
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Message
     */
    public function addRecipient(Recipient $recipient) : self
    {
        $this->recipients[] = $recipient;

        if (!isset($this->recipientType)) {
            $this->recipientType = get_class($recipient);
        }
        if ($this->recipientType !== get_class($recipient)) {
            throw new \InvalidArgumentException('Mixed recipient types are not supported by FCM');
        }

        return $this;
    }

    /**
     * @param Notification $notification
     * @return Message
     */
    public function setNotification(Notification $notification) : self
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * Key used to collapse the previous message sent with the same key and display the new one
     * @param $collapseKey
     * @return $this
     */
    public function setCollapseKey(string $collapseKey) : self
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    /**
     * Value used to give more or less priority to each message, we have two types of priorities: "normal", "high"
     * @param string $priority
     * @return $this
     */
    public function setPriority(string $priority) : self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return bool
     */
    public function getContentAvailable() : bool
    {
        return $this->contentAvailable;
    }

    /**
     * Used to indicate to iOS App when the server want to send information to sync the app
     * @param boolean $contentAvailable
     * @return $this
     */
    public function setContentAvailable(bool $contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setTimeToLive(int $value)
    {
        $this->ttl = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $jsonData = [];

        if (empty($this->recipients)) {
            throw new \UnexpectedValueException('Message must have at least one recipient');
        }

        if (count($this->recipients) == 1) {
            $jsonData['to'] = $this->createTarget();
        } elseif ($this->recipientType == Device::class) {
            $jsonData['registration_ids'] = $this->createTarget();
        } else {
            $jsonData['condition'] = $this->createTarget();
        }

        if ($this->collapseKey) {
            $jsonData['collapse_key'] = $this->collapseKey;
        }
        if ($this->data) {
            $jsonData['data'] = $this->data;
        }
        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }
        if ($this->contentAvailable) {
            $jsonData['content_available'] = $this->contentAvailable;
        }
        if ($this->notification && $this->notification->hasNotificationData()) {
            $jsonData['notification'] = $this->notification;
        }

        return $jsonData;
    }

    /**
     * @return array|null|string
     */
    private function createTarget()
    {
        $recipientCount = count($this->recipients);
        switch ($this->recipientType) {
            case Topic::class:
                if ($recipientCount > self::MAX_TOPICS) {
                    throw new \OutOfRangeException(sprintf('Message topic limit exceeded. Firebase supports a maximum of %u topics.', self::MAX_TOPICS));

                } else if (!$this->condition) {
                    throw new \InvalidArgumentException('Missing message condition. You must specify a condition pattern when sending to combinations of topics.');

                } else if ($recipientCount != substr_count($this->condition, '%s')) {
                    throw new \UnexpectedValueException('The number of message topics must match the number of occurrences of "%s" in the condition pattern.');
                }

                if ($recipientCount == 1) {
                    return sprintf('/topics/%s', current($this->recipients)->getName());
                } else {
                    $names = [];
                    foreach ($this->recipients as $recipient) {
                        $names[] = sprintf("'%s' in topics", $recipient->getName());
                    }
                    return vsprintf($this->condition, $names);
                }

            case Device::class:
                if ($recipientCount > self::MAX_DEVICES) {
                    throw new \OutOfRangeException(sprintf('Message device limit exceeded. Firebase supports a maximum of %u devices.', self::MAX_DEVICES));
                }

                if ($recipientCount == 1) {
                    return current($this->recipients)->getToken();
                } else {
                    return array_map(function(Device $device){
                        return $device->getToken();
                    }, $this->recipients);
                }

            default:
                return null;
                break;
        }
    }
}
