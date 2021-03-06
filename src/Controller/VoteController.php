<?php
/**
 * Created by PhpStorm.
 * User: hqy
 * Date: 2018/7/23
 * Time: 12:51 PM
 */
namespace App\Controller;
use App\Controller\AbstractController;
use App\Entity\Choice;
use App\Entity\Section;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Vote;
use App\Library\LocationHelper;
use App\Library\VoteStatus;
use App\Service\SMService;
use App\Service\VoteManagerService;
use http\Cookie;
use itbdw\Ip\IpLocation;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Translation\TranslatorInterface;

class VoteController extends AbstractController
{
    /**
     * @Route("/", methods="GET")
     */
    public function index() {
        return $this->render("base.html.twig");
    }

    /**
     * @Route("/info")
     */
    public function info(Request $request){
        return $this->response(IpLocation::getLocation($request->getClientIp()));
    }

    /**
     * @Route("/user", methods="GET")
     */
    public function user(Request $request) {
        if (LocationHelper::check($request->getClientIp()))
            return $this->responseEntity($this->getUser());
        else
            return $this->response(null, 403);
    }

    /**
     * @Route("/current", methods="GET")
     */
    public function current() {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $votes = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Vote::class)
            ->findEnabled();
        if(count($votes) == 1)
            return $this->responseEntity($votes[0]);
        else
            return $this->response(null, 404);
    }

    /**
     * @Route("/send", methods="POST")
     */
    public function send(Request $request, SMService $service) {
        if (!LocationHelper::check($request->getClientIp()))
            return $this->response(null);
        if(!$request->request->has("name") || !$request->request->has("phone"))
            return $this->response("Invalid request.", 400);
        if ($request->request->has("confirm")) {
            $this->denyAccessUnlessGranted(User::ROLE_USER);
            $user = $this->getUser();
            $action = "confirm";
        } else {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
                "name" => $request->request->get("name"),
                "phone" => $request->request->get("phone")
            ]);
            $action = "login";
        }
        if (is_null($user))
            return $this->response("找不到用户。", 404);
        if ($request->request->get("secret") == $_ENV["APP_SECRET"])
            $result = $service->sendCode($user, $request->getClientIp(), $action, 000000);
        else
            $result = $service->sendCode($user, $request->getClientIp(), $action);
        if (is_null($result))
            return $this->response("请求频率过高，请在60秒之后再试（" .$request->getClientIp()."）", 403);
        else if ($result)
            return $this->response("发送成功。");
        else
            return $this->response("发送失败，请联系管理员。", 400);
    }

    /**
     * @Route("/login", methods="POST")
     */
    public function authorize(Request $request, SMService $service) {
        if(!$request->request->has("name") || !$request->request->has("phone") || !$request->request->has("code"))
            return $this->response("Invalid request.", 400);
        $session = $request->getSession();
        if (!$session)
            $session = new Session();
        $session->start();

        /** @var User $user */
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            "name" => $request->request->get("name"),
            "phone" => $request->request->get("phone")
        ]);

        $result = $service->verifyCode($user, $request->request->get("code"), "login");
        if(is_null($result))
            return $this->response("验证码已失效，请尝试重新发送。", 403);
        else if($result) {
            $session->set("phone", $user->getPhone());
            return $this->response(null);
        } else {
            return $this->response("验证码不正确。", 400);
        }
    }

    /**
     * @Route("/logout", methods="POST")
     */
    public function logout(Request $request) {
        $request->getSession()->start();
        $request->getSession()->clear();
        //$request->getSession()->invalidate(0);
        $request->getSession()->remove("phone");
        $response = $this->response(null, 200);
        $time = new \DateTime();
        $time->sub(new \DateInterval("P1M"));
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie("PHPSESSID", "deleted", $time, "/", null, false, true));
        return $response;
    }


    /**
     * @Route("/submit", methods="POST")
     */
    public function vote(Request $request, VoteManagerService $voteManagerService, SMService $service) {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        if(!$request->request->has("code"))
            return $this->response("请输入验证码。", Response::HTTP_UNAUTHORIZED);
        if(!$request->request->has("code") || !$request->request->has("data"))
            return $this->response("无效的请求。", 403);

        $em = $this->getDoctrine()->getManager(); // Check if the user has already voted.
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();
        if(!is_null($em->getRepository(Ticket::class)->findOneByUserAndVote($this->getUser(), $vote)))
            return $this->response("您已经投过票了。", Response::HTTP_FORBIDDEN);


        $data = $request->request->get("data");
        $code = $request->request->get("code");

        $info = $this->cryptoJsAesDecrypt($code, $data);

        if(is_null($data))
            return $this->response("无效的请求。", 403);

        $id = $info["id"];
        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("投票不存在，请刷新。", Response::HTTP_NOT_FOUND);
        if($vote->getStatus() != VoteStatus::VOTING)
            return $this->response("投票不存在，请刷新。", Response::HTTP_UNAUTHORIZED);

        $result = $service->verifyCode($this->getUser(), $request->request->get("code"), "confirm");
        if(is_null($result))
            return $this->response("验证码已失效，请尝试重新发送。", 403);
        else if(!$result) {
            return $this->response("验证码不正确。", 400);
        }

        if(!array_key_exists("deviceId", $info) || !array_key_exists("other", $info))
            return $this->response("无效的客户端。",Response::HTTP_BAD_REQUEST);

        try {
            $ticket = new Ticket(
                $vote,
                $this->getUser(),
                $info["choices"],
                ($request->headers->get("X-Forwarded-For") ?? "") . "|" . $request->getClientIp() ,
                $request->headers->get("user-agent"),
                $info["deviceId"],
                $info["other"]);

            $em->persist($ticket);
            $em->flush();

            $service->sendOrder($this->getUser(), $ticket);
            /*
            $info = json_encode([
                "request" => $request->request->all(),
                "query" => $request->query->all(),
                "cookies" => $request->cookies->all(),
                "server" => $request->server->all(),
                "file" => $request->files->all(),
                "user" => $this->getUser()->getInfoArray()
            ]);
            file_put_contents("/var/log/vote.log", $info, FILE_APPEND);
            $this->writeLog("UserVoted", json_encode($request->request->get("choices")));
            */

            return $this->responseEntity($ticket, Response::HTTP_OK);
        }
        catch(\Exception $e) {
            return $this->response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/result", methods="GET")
     */
    public function result(Request $request, VoteManagerService $voteManagerService) {
        $this->denyAccessUnlessGranted(User::ROLE_USER);

        $id = $request->query->get("id");
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();

        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("Your vote does not exist.", Response::HTTP_NOT_FOUND);
        if($vote->getStatus() != VoteStatus::RESULTS_RELEASED)
            return $this->response("Your vote does not exist.", Response::HTTP_UNAUTHORIZED);


        if($vote->getStatus() == VoteStatus::RESULTS_RELEASED) {
            $result = $voteManagerService->result($id, false);
            $tickets = $this->getDoctrine()->getManager()->getRepository(Ticket::class)->findBy(["vote" => $vote]);
            return $this->response([
                "count" => count($tickets),
                "data" => $result
            ]);
        } else {
            return $this->response(null);
        }
    }

    /**
     * @Route("/mine", methods="GET")
     */
    public function mine(Request $request, VoteManagerService $voteManagerService) {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $id = $request->query->get("id");
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();

        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("Your vote does not exist.", Response::HTTP_NOT_FOUND);
        /** @var Choice $choice */
        $choice = $this->getDoctrine()->getManager()->getRepository(Choice::class)->findOneByUserAndVote($this->getUser(), $vote);

        if (!is_null($choice) && $vote->getStatus() == VoteStatus::RESULTS_RELEASED) {
            $info = $choice->jsonSerialize();
            $info["count"] = $choice->getCount();
            $info["adjust"] = $choice->getAdjust();
            $info["result"] = $choice->getResult();
            return $this->response($info);
        } else {
            return $this->responseEntity($choice);
        }
    }

    /**
     * @Route("/voted", methods="GET")
     */
    public function voted(Request $request, VoteManagerService $voteManagerService) {
        $this->denyAccessUnlessGranted(User::ROLE_USER);
        $id = $request->query->get("id");
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();

        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("Your vote does not exist.", Response::HTTP_NOT_FOUND);
        /** @var Choice $choice */
        $ticket = $this->getDoctrine()->getManager()->getRepository(Ticket::class)->findOneByUserAndVote($this->getUser(), $vote);

        if (!is_null($ticket)) {
            return $this->response(true);
        } else {
            return $this->response(false);
        }
    }

    /**
     * @Route("/query")
     */
    public function query(Request $request) {
        if(!LocationHelper::check($request->getClientIp()))
            return Response::create();
        if($request->getMethod() == "POST") {
            $captcha = $request->request->get("g-recaptcha-response");
            if($this->verifyCaptcha($captcha)) {
                /** @var Ticket $ticket */
                $ticket = $this->getDoctrine()->getRepository(Ticket::class)->findOneBy(["code" => $request->request->get("code")]);
                if(is_null($ticket))
                    $info = "找不到选票。";
                else
                    $info = array_reduce($ticket->getChoices()->toArray(), function($prev, $current){
                        return $prev . $current->getName() . "<br/>";
                    }, "");
            } else {
                $info = "验证码不正确。";
            }
        } else {
            $info = "";
        }

        return $this->render("query.html.twig", [
            "data" => $info
        ]);
    }

}