<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Csrf\CsrfToken;
class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function responseListEntities($objects, $code = 200)
    {
        return JsonResponse::fromJsonString(json_encode([
            "code" => $code,
            "data" => array_map(function($data){
                return $data->jsonSerialize();
            }, $objects)
        ]), $code);
    }

    public function responseEntity($object, $code = 200) {
        if(is_null($object)) {
            if($code == 200)
                $code = 404;
            return JsonResponse::fromJsonString(json_encode([
                "code" => $code,
                "data" => null
            ]), $code);
        } else {
            return JsonResponse::fromJsonString(json_encode([
                "code" => $code,
                "data" => $object->jsonSerialize()
            ]), $code);
        }
    }

    public function response($data, $code = 200) {
        return JsonResponse::create([
            "code" => $code,
            "data" => $data
        ], $code);
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return parent::getUser();
    }

    public function setting()
    {
        // return $this->getDoctrine()->getManager()->getRepository(Preference::class);
    }

    public function verifyCaptcha($captcha)
    {
        $verifyResponse = file_get_contents('https://www.recaptcha.net/recaptcha/api/siteverify?secret=' . "6Ldzra8UAAAAAM-OqLHfcFgZkPL_FfnLzoV-tTP0" . '&response=' . $captcha);
        if (json_decode($verifyResponse)->success) {
            return true;
        } else {
            return false;
        }
    }
    public function isValidUuid($uuid)
    {
        if (is_null($uuid))
            return false;
        else if (preg_match('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/', $uuid)) {
            return true;
        } else {
            return false;
        }
    }

    protected function cryptoJsAesDecrypt($passphrase, $jsonString){
        $jsondata = json_decode($jsonString, true);
        try {
            $salt = hex2bin($jsondata["s"]);
            $iv  = hex2bin($jsondata["iv"]);
        } catch(Exception $e) { return null; }
        $ct = base64_decode($jsondata["ct"]);
        $concatedPassphrase = $passphrase.$salt;
        $md5 = array();
        $md5[0] = md5($concatedPassphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1].$concatedPassphrase, true);
            $result .= $md5[$i];
        }
        $key = substr($result, 0, 32);
        $data = openssl_decrypt($ct, 'aes-256-cbc', $key, true, $iv);
        return json_decode($data, true);
    }
}