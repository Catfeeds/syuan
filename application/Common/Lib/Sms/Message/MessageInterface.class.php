<?php
namespace Common\Lib\Sms\Message;

/**
 * Interface MessageInterface
 */
interface MessageInterface {
    /**
     * Message Type: Standard
     */
    const TYPE_STANDARD = 'STANDARD_MESSAGE';

    /**
     * Message Type: Template
     */
    const TYPE_TEMPLATE = 'TEMPLATE_MESSAGE';

    /**
     * @param $recipient
     * @return mixed
     */
    public function setRecipient($recipient);

    /**
     * @return mixed
     */
    public function getRecipient();

    /**
     * @return string
     */
    public function __toString();
}
