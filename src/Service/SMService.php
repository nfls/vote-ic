<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/24
 * Time: 13:09
 */

namespace App\Service;


class SMService
{
    private $client;

    public function __construct()
    {
        $this->client = new \Celery(
            "localhost",
            "",
            "",
            0,
            'celery',
            'celery',
            6379,
            'redis'
        );
    }

    public function send(?string $receiver, string $template, array $params) {
        if(is_null($receiver))
            return false;
        if(!preg_match('`^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\d{8}$`m', $receiver))
            return false;
        try {
            $this->client->PostTask("tasks.sendSMS", array($receiver, $template, $params));
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }
    public function bulk(array $receivers, string $template, array $params) {
        foreach($receivers as $receiver) {
            $this->send($receiver, $template, $params);
        }
    }
}