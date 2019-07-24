<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/24
 * Time: 13:09
 */

namespace App\Service;


use App\Entity\User;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class SMService
{
    private $celery;
    private $redis;

    public function __construct()
    {
        $this->celery = new \Celery(
            "localhost",
            "",
            "",
            0,
            'celery',
            'celery',
            6379,
            'redis'
        );

        $this->redis = RedisAdapter::createConnection('redis://localhost');
    }

    public function sendCode(User $user) {
        $code = $this->getRandomCode();
        if($this->send($user->getPhone(), "", ["code" => $code])) { //TODO: Add template.
            $this->redis->set($this->getKey($user), $code);
            $this->redis->expire($this->getKey($user), 180);
        } else {

        }


    }

    public function verifyCode(User $user, string $code) {

    }

    private function getKey(User $user) {
        return "vote".$user->getPhone();
    }

    private function send(?string $receiver, string $template, array $params) {
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

    private function getRandomCode() {
        return (string)mt_rand(100000, 999999);
    }
}