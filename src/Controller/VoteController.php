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
use App\Library\VoteStatus;
use App\Service\SMService;
use App\Service\VoteManagerService;
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
     * @Route("/user", methods="GET")
     */
    public function user() {
        return $this->responseEntity($this->getUser());
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
            return $this->response("User not found.", 404);
        $result = $service->sendCode($user, $request->getClientIp(), $action);
        if (is_null($result))
            return $this->response("Your rate has hit the limit. Try again 60 seconds later.", 403);
        else if ($result)
            return $this->response("Sent successfully.");
        else
            return $this->response("Failed to send. Please contact admin.", 400);
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
            return $this->response("Expired. Please send the code again.", 403);
        else if($result) {
            $session->set("phone", $user->getPhone());
            return $this->response(null);
        } else {
            return $this->response("Incorrect code.", 400);
        }
    }

    /**
     * @Route("/logout", methods="POST")
     */
    public function logout(Request $request) {

    }

    /**
     * @Route("/submit", methods="POST")
     */
    public function vote(Request $request, VoteManagerService $voteManagerService, SMService $service) {
        $this->denyAccessUnlessGranted(User::ROLE_USER);

        $id = $request->request->get("id");
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();

        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("Your vote does not exist.", Response::HTTP_NOT_FOUND);
        if($vote->getStatus() != VoteStatus::VOTING)
            return $this->response("Your vote does not exist.", Response::HTTP_UNAUTHORIZED);

        $em = $this->getDoctrine()->getManager(); // Check if the user has already voted.

        if(!is_null($em->getRepository(Ticket::class)->findOneByUserAndVote($this->getUser(), $vote)))
            return $this->response("You have already voted.", Response::HTTP_FORBIDDEN);

        if(!$request->request->has("code"))
            return $this->response("Please enter your code.", Response::HTTP_UNAUTHORIZED);

        if(!$service->verifyCode($this->getUser(), $request->request->get("code"), "confirm"))
            return $this->response("Your code is not correct.", Response::HTTP_UNAUTHORIZED);

        if(!$request->request->has("deviceId") || !$request->request->has("other"))
            return $this->response("Invalid client.",Response::HTTP_BAD_REQUEST);

        try {
            $ticket = new Ticket(
                $vote,
                $this->getUser(),
                $request->request->get("choices"),
                ($request->headers->get("X-Forwarded-For") ?? "") . "|" . $request->getClientIp() ,
                $request->headers->get("user-agent"),
                $request->request->get("deviceId"),
                $request->request->get("other"));

            $em->persist($ticket);
            $em->flush();


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

        $id = $request->request->get("id");
        /** @var Vote $vote */
        $vote = $voteManagerService->findCurrent();

        if(is_null($vote) || $vote->getId()->toString() != $id)
            return $this->response("Your vote does not exist.", Response::HTTP_NOT_FOUND);
        if($vote->getStatus() != VoteStatus::RESULTS_RELEASED)
            return $this->response("Your vote does not exist.", Response::HTTP_UNAUTHORIZED);

        $em = $this->getDoctrine()->getManager();

        $tickets = $em->getRepository(Ticket::class)->findBy(["vote" => $vote]);



        return $this->response()->response(array("total" => count($tickets), "detail" => $result));
    }
}