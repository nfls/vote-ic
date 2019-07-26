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
}