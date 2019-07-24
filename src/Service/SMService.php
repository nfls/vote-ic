<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/24
 * Time: 13:09
 */

namespace App\Service;


use App\Entity\Ticket;
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
        if($this->send($user->getPhone(), "SMS_171187527", ["code_begin" => substr($code, 0, 3), "code_end" => substr($code, 3, 3)])) {
            $this->redis->set($this->getKey($user), $code);
            $this->redis->expire($this->getKey($user), 180);
            return true;
        } else {
            return false;
        }
    }

    public function verifyCode(User $user, string $code) {
        if($this->redis->get($this->getKey($user)) == $code) {
            $this->redis->del($this->getKey($user));
            return true;
        } else {
            $this->rate(); //TODO
            return false;
        }
    }

    private function rate() {

    }

    public function sendId(User $user, Ticket $ticket) {
        $this->send($user->getPhone(), "SMS_171192462", ["sn" => $ticket->getCode()]);
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
            $this->celery->PostTask("tasks.sendSMS", array($receiver, $template, $params));
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    private function getRandomCode() {
        return (string)mt_rand(100000, 999999);
    }
}