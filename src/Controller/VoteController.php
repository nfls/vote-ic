<?php
/**
 * Created by PhpStorm.
 * User: hqy
 * Date: 2018/7/23
 * Time: 12:51 PM
 */
namespace App\Controller;
use App\Controller\AbstractController;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Vote;
use App\Library\VoteStatus;
use App\Service\VoteManagerService;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @Route("/current", methods="GET")
     */
    public function current() {
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
     * @Route("/authorize", methods="POST")
     */
    public function authorize(Request $request) {

    }

    /**
     * @Route("/logout", methods="POST")
     */
    public function logout(Request $request) {

    }

    /**
     * @Route("/submit", methods="POST")
     */
    public function vote(Request $request, VoteManagerService $voteManagerService) {
        //$this->denyAccessUnlessGranted(Permission::IS_LOGIN);

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
        try {
            // if(!$passwordEncoder->isPasswordValid($this->getUser(), $request->request->get("password"))) {
            //    return $this->response()->response($translator->trans("incorrect-password"), Response::HTTP_BAD_REQUEST);
            //} // TODO: SMS Verification.

            if(!$request->request->has("deviceId") || !$request->request->has("other")) {
                return $this->response("Invalid client.",Response::HTTP_BAD_REQUEST);
            }

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
        } catch(\Exception $e) {
           return $this->response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/result", methods="GET")
     */
    public function result(Request $request) {

        $this->denyAccessUnlessGranted(Permission::IS_ADMIN);
        $id = $request->request->get("id");
        if($id == 'new')
            return $this->response()->response(null);
        $em = $this->getDoctrine()->getManager();
        /** @var Vote $vote */
        $vote = $em->getRepository(Vote::class)->find($id);
        $tickets = $em->getRepository(Ticket::class)->findBy(["vote" => $vote]);
        $result = array();
        for($i=0; $i<count($vote->getOptions()); $i++) {
            $result[$i] = array();
            for($j=0; $j<count($vote->getOptions()[$i]["options"]); $j++) {
                $result[$i][$j] = 0;
            }
        }
        foreach ($tickets as $ticket) {
            /** @var Ticket $ticket*/
            foreach ($ticket->getChoices() as $key => $choice) {
                $result[$key][$choice] ++;
            }
        }
        return $this->response()->response(array("total" => count($tickets), "detail" => $result));
    }
}