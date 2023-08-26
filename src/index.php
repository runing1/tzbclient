<?php /** @noinspection ALL */

namespace TzbClient;
require_once '../vendor/autoload.php';
require_once 'utils/sm4.php';
require_once 'utils/httpUtils.php';

use Rtgm\sm\RtSm2;
use Rtgm\sm\RtSm3;
use TzbClient\utils\HttpUtils;
use TzbClient\utils\SM4;


class Client
{
    function tokenRequest($requestUrl, $appKey, $appScrect, $param, $publicKey, $privateKey)
    {
        printf("appKey:%s,请求tokenURL:%s\n", $appKey, $requestUrl);
        $sm2 = new RtSm2('hex', true);
        $sm3 = new RtSm3();
        $sm4 = new SM4();
        $uuid = $this->getGUID();
        $tranDate = date("Y-m-d H:i:s.v");
        $inputDic = array("equUniSeqNo" => $param, "randomSec" => $uuid, "tranDate" => $tranDate);
        printf("获取token原始报文:%s\n", json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        $sm3sgin = $sm3->digest(json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        printf("SM3散列:%s\n", $sm3sgin);
        $sign = $sm2->doSign(hex2bin($sm3sgin), $privateKey, hex2bin("1234567812345678"));
        printf("签名:%s\n", $sign);
        $inputDic['x-sign'] = $sign;
        $eyInputAndSginJson = $sm4->setKey($appScrect)->encryptData(json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        printf("加密后请求:%s\n", $sm3sgin);
        $headerMap = array("x-appKey" => $appKey);
        $httpUtils = new HttpUtils();
        $resultDic = $httpUtils->post($requestUrl, $eyInputAndSginJson, $headerMap);
        printf("获取token返回报文:%s\n", json_encode($resultDic, JSON_UNESCAPED_UNICODE));
        if ($resultDic == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "通讯失败"), JSON_UNESCAPED_UNICODE);
        }
        if (!$resultDic["isSsuccess"]) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => $resultDic['returnJson']), JSON_UNESCAPED_UNICODE);
        }
        $outputDic = json_decode($sm4->setKey($appScrect)->decryptData(str_replace("\n", "", $resultDic["returnJson"])), JSON_UNESCAPED_UNICODE);
        if ($outputDic["x-sign"] == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "http通讯缺失返回参数[x-sign]"), JSON_UNESCAPED_UNICODE);
        }
        $sign = $outputDic["x-sign"];
        unset($outputDic["x-sign"]);
        $message = json_encode($outputDic, JSON_UNESCAPED_UNICODE);
        $message3 = $sm3->digest($message);
        $signFlag = $sm2->verifySign(hex2bin($message3), $sign, $publicKey, hex2bin("1234567812345678"));
        if ($signFlag) {
            return $message;
        } else {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => '签名验证不通过！！'), JSON_UNESCAPED_UNICODE);
        }
    }

    function clientRequest($requestUrl, $appKey, $randomSec, $inputDic, $publicKey, $privateKey, $token)
    {
        $sm2 = new RtSm2('hex', true);
        $sm3 = new RtSm3();
        $sm4 = new SM4();
        printf("请求接口url:%s\n", $requestUrl);
        printf("原始报文:%s\n", $inputDic);
        $httpUtils = new HttpUtils();
        $inputDic = json_decode($inputDic, true, 512);
        $inputDic['token'] = $token;
        $inputAndToken = json_encode($inputDic, JSON_UNESCAPED_UNICODE);
        $inputAndToken = str_replace("\\", "", $inputAndToken);
        $sm3sgin = $sm3->digest($inputAndToken);
        printf("sm3散列:%s\n", $sm3sgin);
        $sign = $sm2->doSign(hex2bin($sm3sgin), $privateKey, hex2bin("1234567812345678"));
        printf("签名sign:%s\n", $sign);
        $inputDic['x-sign'] = $sign;
        $headerMap = array("x-appKey" => $appKey, "token" => $token);
        $sm4inputS = $sm4->setKey($randomSec)->encryptData(json_encode($inputDic));
        printf("sm4加密报文:%s\n", $sm3sgin);
        $resultDic = $httpUtils->post($requestUrl, $sm4inputS, $headerMap);
        printf("开放平台返回报文:%s\n", json_encode($resultDic, JSON_UNESCAPED_UNICODE));
        if ($resultDic == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "通讯失败"), JSON_UNESCAPED_UNICODE);
        }
        if (!$resultDic["isSsuccess"]) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => $resultDic['returnJson']), JSON_UNESCAPED_UNICODE);
        }
        $json = $sm4->setKey($randomSec)->decryptData(str_replace("\n", "", $resultDic["returnJson"]));
        $outputDic = json_decode($json, JSON_BIGINT_AS_STRING);
        if ($outputDic["x-sign"] == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "http通讯缺失返回参数[x-sign]"), JSON_UNESCAPED_UNICODE);
        }
        $sign = $outputDic["x-sign"];
        $str = ',"x-sign":';
        $len = strpos($json, $str);
        $length1 = strlen($str);
        $length2 = strlen($sign);
        $length = $length1 + $length2 + 2;
        $strs = substr($json, $len, $length);
        $message = str_replace($strs, "", $json);
        $message3 = $sm3->digest($message);
        $signFlag = $sm2->verifySign(hex2bin($message3), $sign, $publicKey, hex2bin("1234567812345678"));
        if ($signFlag) {
            return $message;
        } else {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => '签名验证不通过！！'), JSON_UNESCAPED_UNICODE);
        }

    }

    function getGUID()
    {

        if (function_exists('com_create_guid')) {

            return strtolower(trim(str_replace("-", "", com_create_guid()), '{}')); //去大括号转小写,weiku.co

        } else {
            die("php_com_dotnet.dll 配置未启用");
        }
    }




    //获取私钥
    function getPrivateKey($privateKeyPath): string
    {
        $privateKeyText = file_get_contents($privateKeyPath);
        $privateInfo = openssl_pkey_get_private($privateKeyText);
        $private_key_details = openssl_pkey_get_details($privateInfo);
        return bin2hex($private_key_details["ec"]["d"]);
    }

    //获取公钥
    function getPublicKey($publicKeyPath): string
    {
        $cer = file_get_contents($publicKeyPath);
        $public_key = openssl_pkey_get_public($cer);
        $public_key_details = openssl_pkey_get_details($public_key);
        return bin2hex($public_key_details['ec']['x']) . bin2hex($public_key_details['ec']['y']);
    }

    //软证书获取token
    function tokenCerRequest($requestUrl, $appKey, $appScrect, $param, $publicKeyPath, $privateKeyPath)
    {
        printf("appKey:%s,获取tokenURL:%s\n", $appKey, $requestUrl);
        $sm2 = new RtSm2('hex', true);
        $sm3 = new RtSm3();
        $sm4 = new SM4();
        $uuid = $this->getGUID();
        $tranDate = date("Y-m-d H:i:s.v");
        $privateKey = $this->getPrivateKey($privateKeyPath);
        $inputDic = array("equUniSeqNo" => $param, "randomSec" => $uuid, "tranDate" => $tranDate);
        $sm3sign = $sm3->digest(json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        printf("SM3散列:%s\n", $sm3sign);
        $sign = $sm2->doSign(hex2bin($sm3sign), $privateKey, hex2bin("1234567812345678"));
        printf("签名sign:%s\n", $sign);
        $inputDic['x-sign'] = $sign;
        $eyInputAndSginJson = $sm4->setKey($appScrect)->encryptData(json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        printf("sm4加密后请求:%s\n", $sm3sign);
        $headerMap = array("x-appKey" => $appKey, "x-sign-type" => "cfca");
        $httpUtils = new HttpUtils();
        $resultDic = $httpUtils->post($requestUrl, $eyInputAndSginJson, $headerMap);
        printf("开放平台返回报文:%s\n", json_encode($resultDic, JSON_UNESCAPED_UNICODE));
        if ($resultDic == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "通讯失败"), JSON_UNESCAPED_UNICODE);
        }
        if (!$resultDic["isSsuccess"]) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => $resultDic['returnJson']), JSON_UNESCAPED_UNICODE);
        }
        $outputDic = json_decode($sm4->setKey($appScrect)->decryptData(str_replace("\n", "", $resultDic["returnJson"])), JSON_UNESCAPED_UNICODE);
        if ($outputDic["x-sign"] == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "http通讯缺失返回参数[x-sign]"), JSON_UNESCAPED_UNICODE);
        }
        $publicKey = $this->getPublicKey($publicKeyPath);
        $sign = $outputDic["x-sign"];
        unset($outputDic["x-sign"]);
        $message = json_encode($outputDic, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
        $message3 = $sm3->digest($message);
        $signFlag = $sm2->verifySign(hex2bin($message3), $sign, $publicKey, hex2bin("1234567812345678"));
        if ($signFlag) {
            return $message;
        } else {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => '签名验证不通过！！'), JSON_UNESCAPED_UNICODE);
        }


    }

    //软证书请求
    function clientCerRequest($requestUrl, $appKey, $randomSec, $inputDic, $publicKeyPath, $privateKeyPath, $token)
    {
        printf("请求接口url:%s\n", $requestUrl);
        printf("原始报文:%s\n", $inputDic);
        $privateKey = $this->getPrivateKey($privateKeyPath);
        $sm2 = new RtSm2('hex', true);
        $sm3 = new RtSm3();
        $sm4 = new SM4();
        $httpUtils = new HttpUtils();
        $inputDic = json_decode($inputDic, true, 512);
        $inputDic['token'] = $token;
        $inputAndToken = json_encode($inputDic, JSON_UNESCAPED_UNICODE);
        $inputAndToken = str_replace("\\", "", $inputAndToken);
        $sm3sgin = $sm3->digest($inputAndToken);
        printf("SM3散列:%s\n", $sm3sgin);
        $sign = $sm2->doSign(hex2bin($sm3sgin), $privateKey, hex2bin("1234567812345678"));
        printf("签名sign:%s\n", $sign);
        $inputDic['x-sign'] = $sign;
        $headerMap = array("x-sign-type" => "cfca", "x-appKey" => $appKey, "token" => $token);
        $sm4inputS = $sm4->setKey($randomSec)->encryptData(json_encode($inputDic, JSON_UNESCAPED_UNICODE));
        printf("sm4加密后请求:%s\n", $sm4inputS);
        $resultDic = $httpUtils->post($requestUrl, $sm4inputS, $headerMap);
        printf("开放平台返回报文:%s\n", json_encode($resultDic, JSON_UNESCAPED_UNICODE));
        if ($resultDic == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "通讯失败"), JSON_UNESCAPED_UNICODE);
        }
        if (!$resultDic["isSsuccess"]) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => $resultDic['returnJson']), JSON_UNESCAPED_UNICODE);
        }
        $json = $sm4->setKey($randomSec)->decryptData(str_replace("\n", "", $resultDic["returnJson"]));
        $outputDic = json_decode($json, JSON_BIGINT_AS_STRING);
        if ($outputDic["x-sign"] == null) {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10002', "retMsg" => "http通讯缺失返回参数[x-sign]"), JSON_UNESCAPED_UNICODE);
        }
        $publicKey = $this->getPublicKey($publicKeyPath);
        $sign = $outputDic["x-sign"];
        $str = ',"x-sign":';
        $len = strpos($json, $str);
        $length1 = strlen($str);
        $length2 = strlen($sign);
        $length = $length1 + $length2 + 2;
        $strs = substr($json, $len, $length);
        $message = str_replace($strs, "", $json);
        $message3 = $sm3->digest($message);
        $signFlag = $sm2->verifySign(hex2bin($message3), $sign, $publicKey, hex2bin("1234567812345678"));
        if ($signFlag) {
            return $message;
        } else {
            return json_encode(array("retType" => 'E', "retCode" => 'sdk10001', "retMsg" => "签名验证不通过！！"), JSON_UNESCAPED_UNICODE);
        }

    }
}
