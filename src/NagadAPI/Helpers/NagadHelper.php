<?php 
namespace NagadAPI\Helpers;

class NagadHelper 
{
    private $nagadMethod;
    private $nagadAccount;
    private $nagadMerchantID;
    private $merchantPrivateKey;
    private $pgPublicKey;
    private $timeZone;
    private $callBackUrl;

    public function __construct($config)
    {
        $this->nagadMethod = $config['NAGAD_METHOD'];
        $this->nagadAccount = $config['NAGAD_APP_ACCOUNT'];
        $this->nagadMerchantID = $config['NAGAD_APP_MERCHANTID'];
        $this->merchantPrivateKey = $config['NAGAD_APP_MERCHANT_PRIVATE_KEY'];
        $this->pgPublicKey = $config['NAGAD_APP_MERCHANT_PG_PUBLIC_KEY'];
        $this->timeZone = $config['NAGAD_APP_TIMEZONE'];
        $this->callBackUrl = $config['NAGAD_CALL_BACK_URL'];
    }

    /**
     * Generate Random string
     */
    public static function generateRandomString($length = 40)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generate public key
     */
    function EncryptDataWithPublicKey($data)
    {
        $pgPublicKey = $this->getPgPublicKey();
        $public_key = "-----BEGIN PUBLIC KEY-----\n" . $pgPublicKey . "\n-----END PUBLIC KEY-----";
        $key_resource = openssl_get_publickey($public_key);
        openssl_public_encrypt($data, $crypttext, $key_resource);
        return base64_encode($crypttext);
    }

    /**
     * Generate signature
     */
    public function SignatureGenerate($data)
    {
        $merchantPrivateKey = $this->getMerchantPrivateKey();
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * get clinet ip
     */
    public function getClientIp()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    /**
     * Decrypt with Private KEY 
     * */
    public function DecryptDataWithPrivateKey($crypttext)
    {
        $merchantPrivateKey = $this->getMerchantPrivateKey();
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
        openssl_private_decrypt(base64_decode($crypttext), $plain_text, $private_key);
        return $plain_text;
    }
    
    /**
     * Custom POST Method 
     */

    public function HttpPostMethod($PostURL, $PostData)
    {
        $url = curl_init($PostURL);
        $posttoken = json_encode($PostData);
        $header = array(
            'Content-Type:application/json',
            'X-KM-Api-Version:v-0.2.0',
            'X-KM-IP-V4:' . $this->getClientIp(),
            'X-KM-Client-Type:PC_WEB'
        );
        
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS, $posttoken);
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($url, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($url, CURLOPT_SSL_VERIFYPEER, 0);
        
        $resultdata = curl_exec($url);
        $ResultArray = json_decode($resultdata, true);
        curl_close($url);
        return $ResultArray;
    }
    
    /**
     * Custom GET Method
     */
     
    public static function HttpGetMethod($url)
    {
        $ch = curl_init();
        $timeout = 10;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/0 (Windows; U; Windows NT 0; zh-CN; rv:3)");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $file_contents = curl_exec($ch);
        echo curl_error($ch);
        curl_close($ch);
        return json_decode($file_contents, true);
    }


    private function generateEnv($config)
    {
        return $config;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getNagadMethod()
    {
        return $this->nagadMethod;
    }

    /**
     * @param mixed $nagadMethod
     * @since v1.3.1
     */
    public function setNagadMethod($nagadMethod)
    {
        $this->nagadMethod = $nagadMethod;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getNagadAccount()
    {
        return $this->nagadAccount;
    }

    /**
     * @param mixed $nagadAccount
     * @since v1.3.1
     */
    public function setNagadAccount($nagadAccount)
    {
        $this->nagadAccount = $nagadAccount;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getCallBackUrl()
    {
        return $this->callBackUrl;
    }

    /**
     * @param mixed $callBackUrl
     * @since v1.3.1
     */
    public function setCallBackUrl($callBackUrl)
    {
        $this->callBackUrl = $callBackUrl;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getNagadMerchantID()
    {
        return $this->nagadMerchantID;
    }

    /**
     * @param mixed $nagadMerchantID
     * @since v1.3.1
     */
    public function setNagadMerchantID($nagadMerchantID)
    {
        $this->nagadMerchantID = $nagadMerchantID;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getMerchantPrivateKey()
    {
        return $this->merchantPrivateKey;
    }

    /**
     * @param mixed $merchantPrivateKey
     * @since v1.3.1
     */
    public function setMerchantPrivateKey($merchantPrivateKey)
    {
        $this->merchantPrivateKey = $merchantPrivateKey;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getPgPublicKey()
    {
        return $this->pgPublicKey;
    }

    /**
     * @param mixed $pgPublicKey
     * @since v1.3.1
     */
    public function setPgPublicKey($pgPublicKey)
    {
        $this->pgPublicKey = $pgPublicKey;
    }

    /**
     * @return mixed
     * @since v1.3.1
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * @param mixed $timeZone
     * @since v1.3.1
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;
    }
}