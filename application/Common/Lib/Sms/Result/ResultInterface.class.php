<?php
namespace Common\Lib\Sms\Result;

use Common\Lib\Sms\Message\MessageInterface;

/**
 * Interface ResultInterface
 */
interface ResultInterface {
    /**
     * The message was received by the provider, and sent to the mobile phone.
     */
    const STATUS_SENT = 'sent';

    /**
     * The message was sent by the provider and delivered to the mobile phone.
     */
    const STATUS_DELIVERED = 'delivered';

    /**
     * The provider failed to send the message.
     */
    const STATUS_FAILED = 'failed';

    /**
     * The message is not sent yet.
     */
    const STATUS_QUEUED = 'queued';

    /**
     * @return int
     */
    public function getSentTimestamp();

    /**
     * @param int $timestamp
     * @return $this
     */
    public function setSentTimestamp($timestamp);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @param MessageInterface $message
     * @return mixed
     */
    public function setMessage(MessageInterface $message);

    /**
     * @return MessageInterface
     */
    public function getMessage();

    /**
     * @param Response $response
     * @return $this
     */
    public function setRawResponse($response);

    /**
     * @return Response
     */
    public function getRawResponse();

    /**
     * @return string
     */
    public function __toString();
}

