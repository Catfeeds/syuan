<?php
namespace Common\Lib\Sms\Providers;

use Common\Lib\Sms\Exception\InvalidNumberException;
use Common\Lib\Sms\Exception\UnsupportedException;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Result\ResultInterface;
use Common\Lib\Sms\Result\StandardResult;
use Common\Lib\Sms\Sender;

class Ali implements ProviderInterface {
	 /**
     * SMS sending API
     */
    const API_URL = 'https://sms.aliyuncs.com/';
    
	/**
    * @var string
    */
    protected $action = 'SingleSendSms';
	
	/**
	*短信签名
    * @var string
    */
    protected $signname = '';

	/**
	*ali密钥
    * @var string
    */
    protected $accesssecret = '';
	
	/**
	*ali密钥ID
    * @var string
    */
    protected $accesskeyid = '';
	
	/**
	*版本号
    * @var string
    */
    protected $version = '2016-09-27';
	
	/**
	*加密方式
    * @var string
    */
    protected $signaturemethod = 'HMAC-SHA1';

	 /**
     * @param $appid
     * @param $appkey
     */
    public function __construct($signname = '', $accesskeyid = '', $accesssecret = '') {
		$this->signname = $signname;
		$this->accesskeyid = $accesskeyid;
		$this->accesssecret = $accesssecret;
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
		$params = array(
			'Format' => 'json',
			'AccessKeyId' => $this->accesskeyid,
			'SignatureMethod' => $this->signaturemethod,
			'SignatureVersion' => '1.0',
			'SignatureNonce' => uniqid(),
			'Action' => $this->action,
			'SignName' => $this->signname,
            'RecNum' => $number, 
			'TemplateCode' => $message->getTemplateId(),
			'ParamString' => json_encode($message->getVars()),
			'Version' => $this->version,
			'Timestamp'	=> date('Y-m-d\TH:i:s\Z'),
        );
		$params['Signature'] = $this->computeSignature($params, $this->accesssecret);
		$response = Sender::sms_curl(self::API_URL, $params, 1);
		$result = new StandardResult($message, $response);
		$responseArr = $result->getRawResponse();
		
		/*恢复时区*/
		date_default_timezone_set("Asia/Shanghai");
		
		if(!isset($responseArr->Code)) {
			return true;
		}
		return false;
	}

    /**
     * @param $number
     * @return ResultInterface
     */
    public function isNumberValid($number) {
		return true;
	}
	
	private function computeSignature($parameters, $accessKeySecret) {
	    ksort($parameters);
	    $canonicalizedQueryString = '';
	    foreach($parameters as $key => $value) {
			$canonicalizedQueryString .= '&' . $this->percentEncode($key). '=' . $this->percentEncode($value);
	    }	
	    $stringToSign = 'POST&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
	    $signature = $this->signString($stringToSign, $accessKeySecret."&");

	    return $signature;
	}
	
	/*签名*/
	private function signString($source, $accessSecret) {
		return	base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
	}
	
	protected function percentEncode($str) {
	    $res = urlencode($str);
	    $res = preg_replace('/\+/', '%20', $res);
	    $res = preg_replace('/\*/', '%2A', $res);
	    $res = preg_replace('/%7E/', '~', $res);
	    return $res;
	}
}