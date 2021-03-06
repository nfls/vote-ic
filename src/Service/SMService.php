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

    public function sendCode(User $user, string $ip, string $action, ?int $code = null) {
        if(is_null($code)) {
            if (!$this->canSend($user, $ip))
                return null;
            $code = $this->getRandomCode();
            if($this->send($user->getPhone(), "login", ["code_begin" => substr($code, 0, 3), "code_end" => substr($code, 3, 3)])) {
                $this->redis->set($this->getKey($user, $action), $code);
                $this->redis->expire($this->getKey($user, $action), 180);
                return true;
            } else {
                return false;
            }
        } else {
            $this->redis->set($this->getKey($user, $action), $code);
            $this->redis->expire($this->getKey($user, $action), 180);
            return true;
        }
    }

    public function sendOrder(User $user, Ticket $ticket) {
        $this->send($user->getPhone(), "confirm", ["sn" => $ticket->getCode()]);
    }

    public function verifyCode(User $user, string $code, string $action) {
        if($this->redis->get($this->getKey($user, $action)) == $code) {
            $this->redis->del($this->getRateKey($user));
            $this->redis->del($this->getKey($user, $action));
            return true;
        } else {
            if($this->rate($user, $action))
                return false;
            else
                return null;
        }
    }

    private function rate(User $user, string $action) {
        $current = (int)$this->redis->get($this->getRateKey($user));
        if($current <= 3) {
            $current++;
            $this->redis->set($this->getRateKey($user), $current);
            return true;
        } else {
            $this->redis->del($this->getRateKey($user));
            $this->redis->del($this->getKey($user, $action));
            return false;
        }
    }

    private function canSend(User $user, string $ip) {
        if ($ip != "222.190.121.121") {
            $current = (int)$this->redis->get($this->getLimitKey($ip));
            if($current >= 10)
                return false;
            $current ++;
            $this->redis->set($this->getLimitKey($ip), $current);
            $this->redis->expire($this->getLimitKey($ip), 600);
        }
        $current = (int)$this->redis->get($this->getLimitKey($user->getPhone()));
        if($current >= 1)
            return false;
        $current ++;
        $this->redis->set($this->getLimitKey($user->getPhone()), $current);
        $this->redis->expire($this->getLimitKey($user->getPhone()), 60);
        return true;
    }

    public function sendId(User $user, Ticket $ticket) {
        $this->send($user->getPhone(), "SMS_171192462", ["sn" => $ticket->getCode()]);
    }

    private function getKey(User $user, string $action) {
        return "vote.".$action.".".$user->getPhone();
    }

    private function getRateKey(User $user) {
        return "try.".$user->getPhone();
    }

    private function getLimitKey(string $target) {
        return "limit.".$target;
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