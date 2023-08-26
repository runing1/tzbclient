<?php
require_once 'index.php';
require_once 'openReq.php';
require_once 'openHeaderRequest.php';

$tzbClient = new TzbClient\Client();
$reqBody = [
    'orderNo' => time(),
    'orderSource' => '02',
    'trxAmt' => 1,
    'currency' => 'CNY',
    'transType' => '2001',
    'tradeMerCstNo' => '8592241714146578500',
    'platMerCstNo' => '8197833247112040502',
    'businessCstNo' => '123456789',
    'goodsInfo' => '2001',
    'payNotifyUrl' => 'https://www.a.com/notify.php'
];
$openReq = new OpenReq();
$head = new OpenHeaderRequest($tzbClient->getGUID(), '123456789');
$openReq->setReqBody($reqBody);
$openReq->setReqHeader($head);
printf(json_encode($openReq, JSON_UNESCAPED_UNICODE));

# 密钥
$token = json_decode($tzbClient->tokenRequest(
    "https://open.tzcb.com:8111/ApiGateWay/auth/getToken",
    "6565c32f-98a8-444b-961d-74479e4a1893",
    "17937356D114742CD388536C2E336E3F",
    "cs",
    "04D49720D0CFBBD00698AF4C5F46B4953299E0AFF495C1EEEA9C0C2C7B05FB430E42DEE136D3C632DF58B5FFB3C0AE8E108B5DBB1DE603A9663A4F00855892D7AE",
    "41BD99F520E376006B5CA4B0792C5E6AF8CC674B7DEC9073553AED050801CBF0"
), true);
printf(json_encode($token, JSON_UNESCAPED_UNICODE));
echo "\n";
$resencrypt =$tzbClient->clientRequest(
    "https://open.tzcb.com:8111/ApiGateWay/apihandle/prod/1.0/payCreateOrder",
    "6565c32f-98a8-444b-961d-74479e4a1893",
    $token["randomSec"],
    json_encode($openReq, JSON_UNESCAPED_UNICODE),
    "04D49720D0CFBBD00698AF4C5F46B4953299E0AFF495C1EEEA9C0C2C7B05FB430E42DEE136D3C632DF58B5FFB3C0AE8E108B5DBB1DE603A9663A4F00855892D7AE",
    "41BD99F520E376006B5CA4B0792C5E6AF8CC674B7DEC9073553AED050801CBF0",
    $token["token"]);
printf(json_encode($resencrypt, JSON_UNESCAPED_UNICODE));




# 软证书
$token = json_decode($tzbClient->tokenCerRequest(
    "https://open.tzcb.com:8111/ApiGateWay/auth/getToken",
    "3235dffb-b720-4756-8ecd-28098a47a746",
    "BCFBB7B6DC2601D67F5A5EE1121B6FF8",
    "cs",
    "D:\\dhcc\\yinqi\\BANKPUB (3).cer",
    "D:\\dhcc\\yinqi\\CUSTPRI (3).txt"
), true);
printf(json_encode($token, JSON_UNESCAPED_UNICODE));
echo "\n";
$resencrypt =$tzbClient->clientCerRequest(
    "https://open.tzcb.com:8111/ApiGateWay/apihandle/prod/1.0/payCreateOrder",
    "3235dffb-b720-4756-8ecd-28098a47a746",
    $token["randomSec"],
    json_encode($openReq, JSON_UNESCAPED_UNICODE),
    "D:\\dhcc\\yinqi\\BANKPUB (3).cer",
    "D:\\dhcc\\yinqi\\CUSTPRI (3).txt",
    $token["token"]);

printf(json_encode($resencrypt, JSON_UNESCAPED_UNICODE));

