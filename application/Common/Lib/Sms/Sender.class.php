<?php
namespace Common\Lib\Sms;

use Common\Lib\Sms\Exception\InvalidNumberException;
use Common\Lib\Sms\Message\StandardMessage;
use Common\Lib\Sms\Message\TemplateMessage;
use Common\Lib\Sms\Providers\ProviderInterface;
/**
 * Class Sender
 */
class Sender {

    /**
     * @var float
     */
    protected static $defaultTimeout = 10;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @param $timeout
     */
    public static function setDefaultTimeout($timeout) {
        self::$defaultTimeout = $timeout;
    }

    /**
     * @param $mobileNumber
     * @return bool
     */
    public static function isMobileNumberValid($mobileNumber) {
        if(strlen($mobileNumber) != 11) {
			return false;
		}
        return true;
    }

    /**
     * @param $mobileNumber
     * @param $templateId
     * @param array $vars
     * @return mixed
     */
    public function sendTemplateMessage($mobileNumber, $templateId, array $vars = array()) {
        $provider = $this->getProvider();
        if(!$provider) {
            throw new \RuntimeException('No provider found');
        }
        if (!self::isMobileNumberValid($mobileNumber)) {
            throw new InvalidNumberException(sprintf("Mobile number %s invalid", $mobileNumber));
        }

        $message = new TemplateMessage($mobileNumber, $templateId, $vars);

        return $provider->sendTemplateMessage($message);
    }

    /**
     * @param $mobileNumber
     * @param $messageBody
     * @return Result\ResultInterface
     */
    public function sendStandardMessage($mobileNumber, $messageBody) {
        $provider = $this->getProvider();
        if (!$provider) {
            throw new \RuntimeException('No provider found');
        }

        if(!self::isMobileNumberValid($mobileNumber)) {
            throw new InvalidNumberException(sprintf("Mobile number %s invalid", $mobileNumber));
        }

        $message = new StandardMessage($mobileNumber, $messageBody);

        return $provider->sendStandardMessage($message);
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider() {
        return $this->provider;
    }

    /**
     * @param ProviderInterface $provider
     * @return $this
     */
    public function setProvider(ProviderInterface $provider) {
        $this->provider = $provider;
        return $this;
    }
	
	/*请求接口*/
	public function sms_curl($url, $params = false, $ispost = 0) {
        $httpInfo = array();
        $ch = curl_init();
 
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'BainiuCms' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , self::$defaultTimeout);
        curl_setopt( $ch, CURLOPT_TIMEOUT , self::$defaultTimeout);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        if($ispost) {
            curl_setopt($ch, CURLOPT_POST , true );
            curl_setopt($ch, CURLOPT_POSTFIELDS , $params);
            curl_setopt($ch, CURLOPT_URL , $url );
        } else {
            if($params) {
                curl_setopt($ch, CURLOPT_URL, $url.'?'.$params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response = curl_exec($ch);
        if($response === FALSE) {
            return false;
        }
        $httpCode = curl_getinfo($ch , CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
		curl_close($ch);
		return $response;
    }
}