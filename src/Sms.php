<?php
// +------------------------------------------------+
// |http://www.cjango.com                           |
// +------------------------------------------------+
// | 修复BUG不是一朝一夕的事情，等我喝醉了再说吧！  |
// +------------------------------------------------+
// | Author: 小陈叔叔 <Jason.Chen>                  |
// +------------------------------------------------+
namespace cjango\aliyun;

use GuzzleHttp\Client;
use think\Config;

class Sms
{
    private $apiUri           = 'https://dysmsapi.aliyuncs.com/';
    private $SignatureMethod  = 'HMAC-SHA1';
    private $SignatureVersion = '1.0';
    private $Format           = 'JSON';
    private $Version          = '2017-05-25';
    private $RegionId         = 'cn-hangzhou';
    private $AccessKeyId      = '';
    private $AccessKeySecret  = '';

    public function __construct()
    {
        $config = Config::get('alisms');
        if (!isset($config['AccessKeyId']) || !isset($config['AccessKeySecret']) || empty($config['AccessKeyId']) || empty($config['AccessKeySecret'])) {
            throw new \Exception('缺少配置参数，或配置参数为空');
        }
        $this->AccessKeyId     = $config['AccessKeyId'];
        $this->AccessKeySecret = $config['AccessKeySecret'];
    }

    /**
     * 返回当前实例
     * @return [type] [description]
     */
    public static function instance()
    {
        return new self;
    }

    /**
     * [发送短信]
     * @param  string $recive        [接收号码]
     * @param  string $sign          [短信签名]
     * @param  string $tplCode       [模板ID]
     * @param  array  $TemplateParam [模板变量]
     * @param  string $OutId         [外部ID]
     */
    public function send(string $recive, string $sign, string $tplCode, array $TemplateParam = [], string $OutId = '')
    {
        $params                  = [];
        $params["PhoneNumbers"]  = $recive;
        $params["SignName"]      = $sign;
        $params["TemplateCode"]  = $tplCode;
        $params['TemplateParam'] = $TemplateParam;
        $params['OutId']         = $OutId;

        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        } else {
            unset($params['TemplateParam']);
        }
        return $this->request($params);
    }

    /**
     * 生成签名并发起请求
     */
    public function request($params)
    {
        $apiParams = array_merge([
            "Action"           => "SendSms",
            "AccessKeyId"      => $this->AccessKeyId,
            "SignatureMethod"  => $this->SignatureMethod,
            "SignatureVersion" => $this->SignatureVersion,
            "Format"           => $this->Format,
            "Version"          => $this->Version,
            "RegionId"         => $this->RegionId,
            "SignatureNonce"   => uniqid(mt_rand(0, 0xffff), true),
            "Timestamp"        => gmdate("Y-m-d\TH:i:s\Z"),
        ], $params);

        $apiParams['Signature'] = self::sign($apiParams);

        $client   = new Client();
        $response = $client->request('GET', $this->apiUri, ['query' => $apiParams, 'headers' => ["x-sdk-client" => "php/2.0.0"], 'verify' => false]);
        $body     = (string) $response->getBody();
        $body     = json_decode($body);

        if ($body->Code == 'OK') {
            return true;
        } else {
            throw new \Exception($body->Message);
        }
    }

    private function sign($params)
    {
        ksort($params);
        $strTmp    = http_build_query($params);
        $strToSign = "GET&%2F&" . urlencode($strTmp);
        return base64_encode(hash_hmac("sha1", $strToSign, $this->AccessKeySecret . "&", true));
    }
}
