<?php

namespace Wisdech\SMSVerify;

use Illuminate\Support\Facades\Cache;
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\SmsClient;

class SMSVerify
{
    const cachePrefix = 'wisdech_sms_verify_';
    private string $signature;

    public function __construct()
    {
        $this->signature = config('verify.signature');
    }

    /**
     * 发送验证码
     * @param $mobile
     * @return mixed
     */
    public function sendVerifyCode($mobile)
    {
        $driver = config('verify.driver');
        $expiredAt = config('verify.expired_at');

        $code = $this->makeVerifyCode($mobile, $expiredAt);

        return $this->$driver($mobile, $code, $expiredAt);
    }

    /**
     * 比对验证码
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function checkVerifyCode($mobile, $code): bool
    {
        $cache = Cache::get(self::cachePrefix . $mobile);
        if ($cache == $code) {
            Cache::forget(self::cachePrefix . $mobile);
            return true;
        }
        return false;
    }

    private function makeVerifyCode($mobile, $expiredAt): string
    {
        $code = "";
        $length = mt_rand(4, 6);
        for ($i = 0; $i < $length; $i++) {
            $code .= mt_rand(0, 9);
        }

        Cache::put(self::cachePrefix . $mobile, $code, now()->addMinutes($expiredAt));

        return $code;
    }

    private function tencent(string $mobile, string $code, string $ttl)
    {
        $cred = new Credential(
            config('verify.drivers.tencent.secret_id'),
            config('verify.drivers.tencent.secret_key'),
        );
        $smsClient = new SmsClient(
            $cred,
            config('verify.drivers.tencent.region')
        );

        $sdkAppId = config('verify.drivers.tencent.sms_app_id');
        $templateId = config('verify.drivers.tencent.template_id');

        $params = json_encode([
            "PhoneNumberSet" => [$mobile],
            "SignName" => $this->signature,
            "SmsSdkAppId" => $sdkAppId,
            "TemplateId" => $templateId,
            "TemplateParamSet" => ["$code", "$ttl"]
        ]);

        $request = new SendSmsRequest();
        $request->fromJsonString($params);

        return $smsClient->sendSms($request);
    }
}
