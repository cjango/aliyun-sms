# aliyun-sms
===============

#### Aliyun 短信模块 For thinkphp5

> 该项目属于于thinkphp5.0.*扩展，模块采用单例模式，调用方法简单

## 安装
> composer require cjango/aliyun-sms

```
"repositories":[
    {
       "type":"git",
       "url":"https://github.com/cjango/aliyun-sms"
    }
]
```

## 配置
> 配置文件位于 `application/extra/alisms.php`

```
return [
    'AccessKeyId'     => '',
    'AccessKeySecret' => '',
];
```

## 使用方法
```
// 引用命名空间
use cjango\aliyun\Sms;

// 获取全部关注用户
Sms::instance()->send($recive, $sign, $tplCode, $templateParam, $outId);

// 参数说明

$recive    接收手机号码
$sign      短信签名（阿里云中申请）
$tplCode   短信模板ID
$templateParam 模板参数（如果有） ['code' => 1234]
$outId     系统序列号（自行生成，可查询用）

// 短信发送成功后的结果参数
Sms::instance()->result;
```
