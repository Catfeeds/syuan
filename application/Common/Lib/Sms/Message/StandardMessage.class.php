<?php
namespace Common\Lib\Sms\Message;
/**
 * Class StandardMessage
 */
class StandardMessage implements MessageInterface {
    /**
     * @var string Mobile phone number
     */
    protected $recipient;

    /**
     * @var string Mobile content
     */
    protected $body;

    /**
     * @param $recipient
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function __construct($recipient, $body)
    {
        $this->recipient = $recipient;
        $this->body = $body;
    }

    public function __toString()
    {
        return json_encode(array(
            'type' => MessageInterface::TYPE_STANDARD,
            'recipient' => $this->recipient,
            'body' => $this->body,
        ));
    }
}