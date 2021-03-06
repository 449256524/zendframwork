<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Cloud
 */

namespace ZendCloud\QueueService\Adapter;

use Traversable;
use ZendCloud\QueueService\Exception;
use ZendCloud\QueueService\Message;
use ZendService\Amazon\Sqs\Sqs as AmazonSqs;
use Zend\Stdlib\ArrayUtils;

/**
 * SQS adapter for simple queue service.
 *
 * @category   Zend
 * @package    Zend_Cloud_QueueService
 * @subpackage Adapter
 */
class Sqs extends AbstractAdapter
{
    /*
     * Options array keys for the SQS adapter.
     */
    const AWS_ACCESS_KEY = 'aws_accesskey';
    const AWS_SECRET_KEY = 'aws_secretkey';

    /**
     * Defaults
     */
    const CREATE_TIMEOUT = 30;

    /**
     * SQS service instance.
     * @var \ZendService\Amazon\Sqs\Sqs
     */
    protected $_sqs;

    /**
     * Constructor
     *
     * @param  array|Traversable $options
     */
    public function __construct($options = array())
    {

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException('Invalid options provided');
        }

        if (isset($options[self::MESSAGE_CLASS])) {
            $this->setMessageClass($options[self::MESSAGE_CLASS]);
        }

        if (isset($options[self::MESSAGESET_CLASS])) {
            $this->setMessageSetClass($options[self::MESSAGESET_CLASS]);
        }

        try {
            $this->_sqs = new AmazonSqs(
                $options[self::AWS_ACCESS_KEY], $options[self::AWS_SECRET_KEY]
            );
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RunTimeException('Error on create: '.$e->getMessage(), $e->getCode(), $e);
        }

        if(isset($options[self::HTTP_ADAPTER])) {
            $this->_sqs->getHttpClient()->setAdapter($options[self::HTTP_ADAPTER]);
        }
    }

     /**
     * Create a queue. Returns the ID of the created queue (typically the URL).
     * It may take some time to create the queue. Check your vendor's
     * documentation for details.
     *
     * @param  string $name
     * @param  array  $options
     * @return string Queue ID (typically URL)
     */
    public function createQueue($name, $options = null)
    {
        try {
            return $this->_sqs->create($name, $options[self::CREATE_TIMEOUT]);
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on queue creation: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Delete a queue. All messages in the queue will also be deleted.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return boolean true if successful, false otherwise
     */
    public function deleteQueue($queueId, $options = null)
{
        try {
            return $this->_sqs->delete($queueId);
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on queue deletion: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * List all queues.
     *
     * @param  array $options
     * @return array Queue IDs
     */
    public function listQueues($options = null)
    {
        try {
            return $this->_sqs->getQueues();
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on listing queues: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get a key/value array of metadata for the given queue.
     *
     * @param  string $queueId
     * @param  array  $options
     * @return array
     */
    public function fetchQueueMetadata($queueId, $options = null)
    {
        try {
            // TODO: ZF-9050 Fix the SQS client library in trunk to return all attribute values
            $attributes = $this->_sqs->getAttribute($queueId, 'All');
            if(is_array($attributes)) {
                return $attributes;
            } else {
                return array('All' => $this->_sqs->getAttribute($queueId, 'All'));
            }
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on fetching queue metadata: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Store a key/value array of metadata for the specified queue.
     * WARNING: This operation overwrites any metadata that is located at
     * $destinationPath. Some adapters may not support this method.
     *
     * @param  array  $metadata
     * @param  string $queueId
     * @param  array  $options
     * @return void
     */
    public function storeQueueMetadata($queueId, $metadata, $options = null)
    {
        // TODO Add support for SetQueueAttributes to client library
        throw new Exception\OperationNotAvailableException('Amazon SQS doesn\'t currently support storing metadata');
    }

    /**
     * Send a message to the specified queue.
     *
     * @param  string $message
     * @param  string $queueId
     * @param  array  $options
     * @return string Message ID
     */
    public function sendMessage($queueId, $message, $options = null)
    {
        try {
            return $this->_sqs->send($queueId, $message);
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on sending message: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Receive at most $max messages from the specified queue and return the
     * message IDs for messages received.
     *
     * @param  string $queueId
     * @param  int    $max
     * @param  array  $options
     * @return array
     */
    public function receiveMessages($queueId, $max = 1, $options = null)
    {
        try {
            return $this->_makeMessages($this->_sqs->receive($queueId, $max, $options[self::VISIBILITY_TIMEOUT]));
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on receiving messages: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Create \ZendCloud\QueueService\Message array for
     * Sqs messages.
     *
     * @param array $messages
     * @return \ZendCloud\QueueService\Message[]
     */
    protected function _makeMessages($messages)
    {
        $messageClass = $this->getMessageClass();
        $setClass     = $this->getMessageSetClass();
        $result = array();
        foreach($messages as $message) {
            $result[] = new $messageClass($message['body'], $message);
        }
        return new $setClass($result);
    }

    /**
     * Delete the specified message from the specified queue.
     *
     * @param  string $queueId
     * @param  \ZendCloud\QueueService\Message $message
     * @param  array  $options
     * @return void
     */
    public function deleteMessage($queueId, $message, $options = null)
    {
        try {
            if($message instanceof Message) {
                $message = $message->getMessage();
            }
            $messageId = $message['handle'];
            return $this->_sqs->deleteMessage($queueId, $messageId);
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on deleting a message: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Peek at the messages from the specified queue without removing them.
     *
     * @param  string $queueId
     * @param  int $num How many messages
     * @param  array  $options
     * @return \ZendCloud\QueueService\Message[]
     */
    public function peekMessages($queueId, $num = 1, $options = null)
    {
        try {
            return $this->_makeMessages($this->_sqs->receive($queueId, $num, 0));
        } catch(\ZendService\Amazon\Exception $e) {
            throw new Exception\RuntimeException('Error on peeking messages: '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get SQS implementation
     * @return \ZendService\Amazon\Sqs\Sqs
     */
    public function getClient()
    {
        return $this->_sqs;
    }
}
