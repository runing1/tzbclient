<?php

namespace TzbClient\Utils;

use HTTP_Request2;

class HttpUtils
{

    function post($requestUrl, $message, $header)
    {
        $request = new HTTP_Request2();
        $request->setUrl($requestUrl);
        $request->setMethod(HTTP_Request2::METHOD_POST);
        $request->setConfig(array(
            'ssl_verify_peer' => FALSE,
            'ssl_verify_host' => FALSE
        ));
        $header['Content-Type'] = 'application/json';
        $request->setHeader($header);
        $request->setBody($message);
        $resultDic = null;
        try {
            $response = $request->send();
            if ($response->getStatus() == 200) {
                $resultDic = array("isSsuccess" => true, 'returnJson' => $response->getBody());
            } elseif ($response->getStatus() == 601) {
                $url64 = urldecode($response->getHeader("errBody"));
                $resultDic = array("isSsuccess" => false, 'returnJson' => base64_decode($url64));
            } else {
                echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                    $response->getReasonPhrase();
                $resultDic = array("isSsuccess" => false, 'returnJson' => $response->getBody());
            }
        } catch (HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
        return $resultDic;
    }
}
