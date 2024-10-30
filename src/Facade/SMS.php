<?php

namespace Wisdech\SMSVerify\Facade;

use Illuminate\Support\Facades\Facade;
use Wisdech\SMSVerify\SMSVerify;

/**
 * @method static mixed sendVerifyCode(string $mobile)
 * @method static bool checkVerifyCode(string $mobile, string $code)
 */
class SMS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SMSVerify::class;
    }
}