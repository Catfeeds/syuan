<?php
namespace Common\Lib\Sms\Providers;

use Common\Lib\Sms\Exception\InvalidNumberException;
use Common\Lib\Sms\Exception\UnsupportedException;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Result\ResultInterface;
use Common\Lib\Sms\Result\StandardResult;
use Common\Lib\Sms\Sender;

class Soap implements ProviderInterface {
	 /**
     * SMS sending API
     */
    const API_URL = 'http://service2.winic.org:8003/Service.asmx?WSDL';

	/**
     * @var string
     */
    protected $soap_uid = 'jihaoba';
	/**
     * @var string
     */
    protected $soap_pwd = 'BNjhb8038';

	 /**
     * @param StandardMessage $message
     * @return ResultInterface
     */
    public function sendStandardMessage(StandardMessage $message) {
		$number = $message->getRecipient();
        if(!$this->isNumberValid($number)) {
            throw new InvalidNumberException(sprintf('Mobile number %s not valid by provider %s', $number, 'sopa'));
        }
		if(!$this->isCountrySupported($number)) {
            throw new UnsupportedException(sprintf('Mobile number %s not supported by provider %s',$number,'soap'));
        }
		
		$param = array(
			'uid' => $this->soap_uid,
			'pwd' => $this->soap_pwd,
			'tos' => $number,
			'msg' => $message->getBody(),
			'otime' => ''
		);
		$client = new \SoapClient(self::API_URL);
		$response = $client->__soapCall('SendMessages', array('parameters' => $param));

        $result = new StandardResult($message, $response);
        if($response !== false) {
			$result->setStatus(ResultInterface::STATUS_DELIVERED);
		} else {
			$result->setStatus(ResultInterface::STATUS_FAILED);
		}
		return $result;
	}

    /**
     * @param TemplateMessage $message
     * @return mixed
     */
    public function sendTemplateMessage(TemplateMessage $message) {
		return true;
	}

    /**
     * @param $number
     * @return ResultInterface
     */
    public function isNumberValid($number) {
		return true;
	}
}
