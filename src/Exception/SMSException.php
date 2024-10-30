<?php

namespace Wisdech\SMSVerify\Exception;

use Exception;

class SMSException extends Exception
{
    public function __construct(string $driver, string $message)
    {
        $message = "[$driver]短信接口调用失败：$message";

        parent::__construct($message);
    }
}