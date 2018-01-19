<?php
namespace Common\Lib\Sms\Result;

use Common\Lib\Sms\Message\MessageInterface;

/**
 * Class StandardResult
 */
class StandardResult implements ResultInterface {
    /**
     * @var string
     */
    protected $status = ResultInterface::STATUS_QUEUED;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var Response
     */
    protected $rawResponse;

    /**
     * @var int
     */
    protected $sentTimestamp;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $supportedStatus = array(
            ResultInterface::STATUS_DELIVERED,
            ResultInterface::STATUS_FAILED,
            ResultInterface::STATUS_QUEUED,
            ResultInterface::STATUS_SENT,
        );

        if (in_array($status, $supportedStatus)) {
            $this->status = $status;
        }
        return $this;
    }

    /**
     * @param MessageInterface $message
     * @return $this
     */
    public function setMessage(MessageInterface $message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setRawResponse($response)
    {
        $this->rawResponse = json_decode($response);
        return $this;
    }

    /**
     * @return Response
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @return int
     */
    public function getSentTimestamp()
    {
        return $this->sentTimestamp;
    }

    /**
     * @param int $timestamp
     * @return $this
     */
    public function setSentTimestamp($timestamp)
    {
        $this->sentTimestamp = (int) $timestamp;
        return $this;
    }

    /**
     * @param MessageInterface $message
     * @param Response $rawResponse
     * @param int $timestamp
     */
    public function __construct(MessageInterface $message, $rawResponse, $timestamp = null)
    {
        $this->setMessage($message);
        $this->setRawResponse($rawResponse);
        $this->sentTimestamp = $timestamp ?: time();
    }


    /**
     * @return string
     */
    public function __toString()
    {
        $result = array(
            'message' => (string) $this->getMessage(),
            'sentTime' => $this->sentTimestamp,
            'result' => $this->getRawResponse(),
        );

        return json_encode($result);
    }
}
