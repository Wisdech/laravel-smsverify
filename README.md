# Laravel 短信验证码接口

## 安装使用

```bash
#添加依赖
composer require wisdech/laravel-smsverify
```

```bash
#发布配置文件
php artisan vendor:publish --tag=sms-verify-config
```

```dotenv
#在.env文件填写配置信息
SMS_SIGNATURE=

TENCENT_CLOUD_ID=
TENCENT_CLOUD_KEY=
TENCENT_SMS_APPID=
TENCENT_SMS_TEMPLATE=
```

```php
//使用Facade
use Wisdech\SMS\Facade\SMS

SMS::sendVerifyCode($mobile);

SMS::checkVerifyCode($mobile,$code);
```

## 短信模板要求

必须包含验证码和有效期，可直接使用模板
```
{1}为您的登录验证码，请于{2}分钟内填写，如非本人操作，请忽略本短信。
```