<?php
namespace Common\Lib\Sms\Providers;

use Common\Lib\Sms\Exception\InvalidNumberException;
use Common\Lib\Sms\Exception\UnsupportedException;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Result\ResultInterface;
use Common\Lib\Sms\Result\StandardResult;
use Common\Lib\Sms\Sender;

class Alidayu implements ProviderInterface {
	
	protected $gatewayUrl = "http://gw.api.taobao.com/router/rest";
	
	protected $apimethodname = "alibaba.aliqin.fc.sms.num.send";
	
	protected $appkey;

	protected $secretKey;

	protected $format = "json";

	protected $smsFreeSignName = '';

	protected $signMethod = "md5";

	protected $apiVersion = "2.0";

	protected $sdkVersion = "top-sdk-php-20151012";
	
    public function __construct($signname = '', $appkey = '', $secretKey = '') {
		$this->smsFreeSignName = $signname;
		$this->appkey = $appkey;
		$this->secretKey = $secretKey ;
    }

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
            throw new InvalidNumberException(sprintf('Mobile number %s not valid by provider %s', $number, 'ali'));
        }
		/*修改时区*/
		date_default_timezone_set("GMT");
		
		$params["partner_id"] = $this->sdkVersion;
		$params["app_key"] = $this->appkey;
		$params["v"] = $this->apiVersion;
		$params["format"] = $this->format;
		$params["sign_method"] = $this->signMethod;
		$params["method"] = $this->apimethodname;
		$params["timestamp"] = date("Y-m-d H:i:s");
		$params["sms_type"] = 'normal';
		$params["sms_free_sign_name"] = $this->smsFreeSignName;
		$params["sms_param"] = json_encode($message->getVars());
		$params["rec_num"] = $number;
		$params["sms_template_code"] = $message->getTemplateId();
		$params["sign"] = $this->generateSign($params);

		$response = Sender::sms_curl($this->gatewayUrl, $params, 1);
		$result = new StandardResult($message, $response);
		$responseArr = $result->getRawResponse();
		
		/*恢复时区*/
		date_default_timezone_set("Asia/Shanghai");
		
		if(!isset($responseArr->error_response)) {
			return true;
		} else {
			return $responseArr->error_response->sub_msg;
		}
	}

    /**
     * @param $number
     * @return ResultInterface
     */
    public function isNumberValid($number) {
		return true;
	}

	protected function generateSign($params) {
		ksort($params);
		$stringToBeSigned = $this->secretKey;
		foreach($params as $k => $v) {
			if(is_string($v) && "@" != substr($v, 0, 1)) {
				$stringToBeSigned .= "$k$v";
			}
		}
		unset($k, $v);
		$stringToBeSigned .= $this->secretKey;
		return strtoupper(md5($stringToBeSigned));
	}
}