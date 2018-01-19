<?php
namespace Common\Lib\Sms\Message;

/**
 * Class TemplateMessage
 */
class TemplateMessage implements MessageInterface {
    /**
     * @var string
     */
    protected $recipient;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var array
     */
    protected $vars = array();

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
     * @param $templateId
     * @return $this
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @param $vars
     * @return $this
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
        return $this;
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * @param $recipient
     * @param $templateId
     * @param array $vars
     */
    public function __construct($recipient, $templateId, array $vars = array())
    {
		$this->recipient = $recipient;
        $this->templateId = $templateId;
        $this->vars = $vars;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
            'type' => MessageInterface::TYPE_TEMPLATE,
            'recipient' => $this->recipient,
            'templateId' => $this->templateId,
            'vars' => $this->vars,
        ));
    }
}