<?php
namespace Common\Lib\Sms\Providers;

use Common\Lib\Sms\Exception\InvalidNumberException;
use Common\Lib\Sms\Exception\UnsupportedException;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Result\ResultInterface;
use Common\Lib\Sms\Result\StandardResult;
use Common\Lib\Sms\Sender;

class Juhe implements ProviderInterface {
	 /**
     * SMS sending API
     */
    const API_URL = 'http://v.juhe.cn/sms/send';
    
	/**
    * @var string
    */
    protected $tpl_id = '';
	
	/**
    * @var string
    */
    protected $tpl_value = '';
	
	/**
     * @var string
     */
    protected $appkey = '';

	 /**
     * @param StandardMessage $message
     * @return ResultInterface
     */
    public function sendStandardMessage(StandardMessage $message) {
		return true;
	}

    /**
     * @param TemplateMessage $message
     * @return mixed
     */
    public function sendTemplateMessage(TemplateMessage $message) {
		$number = $message->getRecipient();
        if(!$this->isNumberValid($number)) {
            throw new InvalidNumberException(sprintf('Mobile number %s not valid by provider %s', $number, 'juhe'));
        }
		
		$params = array(
			'key'		=> $this->appkey,
            'mobile'    => $number, 
			'tpl_id'    => $message->getTemplateId(),
			'tpl_value' => json_encode($message->getVars())
        );
		
		$response = Sender::sms_curl(self::API_URL, array('form' => $params), 1);
        $result = new StandardResult($message, $response);
		$responseArr = $result->getRawResponse();
        if(isset($responseArr->error_code)) {
            if($responseArr->error_code == 0) {
                $result->setStatus(ResultInterface::STATUS_DELIVERED);
            } else {
                $result->setStatus(ResultInterface::STATUS_FAILED);
            }
        }
		return $result;
	}

    /**
     * @param $number
     * @return ResultInterface
     */
    public function isNumberValid($number) {
		return true;
	}
}