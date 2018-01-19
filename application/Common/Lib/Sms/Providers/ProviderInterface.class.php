<?php
namespace Common\Lib\Sms\Providers;

use Common\Lib\Sms\Message\MessageInterface;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Result\ResultInterface;
/**
 * Interface ProviderInterface
 */
interface ProviderInterface{
    /**
     * @param TemplateMessage $message
     * @return mixed
     */
    public function sendTemplateMessage(TemplateMessage $message);

    /**
     * @param StandardMessage $message
     * @return ResultInterface
     */
    public function sendStandardMessage(StandardMessage $message);

    /**
     * @param $number
     * @return ResultInterface
     */
    public function isNumberValid($number);
}