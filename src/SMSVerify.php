<?php

namespace Wisdech\SMSVerify;

use Illuminate\Support\Facades\Cache;
use TencentCloud\Common\Credential;
use TencentCloud\Sms\V20210111\Models\SendSmsRequest;
use TencentCloud\Sms\V20210111\Models\SendSmsResponse;
use TencentCloud\Sms\V20210111\SmsClient;
use Wisdech\SMSVerify\Exception\SMSException;

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
     * @return bool
     * @throws SMSException
     */
    public function sendVerifyCode($mobile): bool
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
        $cacheKey = self::cachePrefix . $mobile;

        $cacheCode = Cache::get($cacheKey);

        if ($cacheCode == $code) {
            Cache::forget($cacheKey);
            return true;
        } else {
            return false;
        }
    }

    private function makeVerifyCode($mobile, $expiredAt): string
    {
        $ttl = now()->addMinutes($expiredAt);
        $cacheKey = self::cachePrefix . $mobile;

        if (Cache::has($cacheKey)) {
            $code = Cache::get($cacheKey);
        } else {

            $code = "";
            for ($i = 0; $i < 6; $i++) {
                $code .= mt_rand(0, 9);
            }
        }

        Cache::put($cacheKey, $code, $ttl);

        return $code;
    }

    /**
     * @param string $mobile
     * @param string $code
     * @param string $ttl
     * @return true
     * @throws SMSException
     */
    private function tencent(string $mobile, string $code, string $ttl): bool
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
        $response = $smsClient->sendSms($request);

        $status = $response->getSendStatusSet();
        if (sizeof($status) > 0 && key_exists('Code', $status[0])) {

            if ($status[0]['Code'] == 'Ok') {
                return true;
            } else {

                $message = $status[0]['Message'];
                throw new SMSException('腾讯云', $message);
            }
        }

        throw new SMSException('腾讯云', "未知错误");
    }
}
