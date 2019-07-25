<?php
/**
 * Created by PhpStorm.
 * User: huqin
 * Date: 2019/7/23
 * Time: 18:14
 */

namespace App\Service;


use App\Entity\Choice;
use App\Entity\Section;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Vote;
use App\Library\VoteStatus;
use Doctrine\Common\Persistence\ObjectManager;

class VoteManagerService
{
    private $objectManager;

    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Array Structure:
     * [
     *      "1. President": ["Leigh Smith", "Leigh Smith"],
     *      "2. Cleaner": ["Walter Nagles", "Walter Nagles"]
     * ]
     */
    public function create(string $name, array $data) {
        $vote = new Vote();
        $vote->setTitle($name);
        $vote->setContent("");
        $vote->setStatus(VoteStatus::HIDDEN);
        foreach ($data as $key => $value) {
            $section = new Section();
            $section->setName($key);
            foreach ($value as $name) {
                $option = new Choice();
                $option->setName($name);
                $section->addChoice($option);
                $this->objectManager->persist($option);
            }
            $vote->addSection($section);
            $this->objectManager->persist($section);
        }
        $this->objectManager->persist($vote);
        $this->objectManager->flush();
        return $vote->getId()->toString();
    }

    public function set(string $id, int $status) {
        $current = $this->findCurrent();
        if (!is_null($current) && $status != VoteStatus::HIDDEN && $current->getId()->toString() != $id) {
            throw new \InvalidArgumentException("You can only show one vote at once.");
        }
        $vote = $this->retrieve($id);
        $vote->setStatus($status);
        $this->objectManager->persist($vote);
        $this->objectManager->flush();
        return true;
    }

    public function retrieve(string $id): Vote {
        $vote = $this->objectManager->getRepository(Vote::class)->find($id);
        if (is_null($vote))
            throw new \Exception("Vote cannot be found");
        return $vote;
    }

    public function associate(string $userId, string $id, bool $add = true) {
        /** @var Choice $choice */
        $choice = $this->objectManager->getRepository(Choice::class)->find($id);
        /** @var User $user */
        $user = $this->objectManager->getRepository(User::class)->find($userId);
        if (is_null($user))
            $user = $this->objectManager->getRepository(User::class)->findOneBy(["name" => $userId]);

        if (is_null($choice))
            throw new \InvalidArgumentException("Cannot find the choice.");

        if (is_null($user))
            throw new \InvalidArgumentException("Cannot find the user");

        if ($add)
            $user->addCandidate($choice);
        else
            $user->removeCandidate($choice);

        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function calculate(string $id) {
        $vote = $this->retrieve($id);
        foreach ($vote->getSections() as $section) {
            foreach ($section->getChoices() as $choice) {
                /** @var Choice $choice */
                $choice->resetCount();
                $this->objectManager->persist($choice);
            }
        }
        $this->objectManager->flush();
        $tickets = $this->objectManager->getRepository(Ticket::class)->findBy(["vote" => $vote]);
        foreach($tickets as $ticket) {
            /** @var Ticket $ticket */
            foreach($ticket->getChoices() as $choice) {
                /** @var Choice $choice */
                $choice->addCount();
                $this->objectManager->persist($choice);
            }
        }
        $this->objectManager->flush();
    }

    public function result(string $id, bool $verbose = false) {
        $vote = $this->retrieve($id);
        if(!$verbose) {
            foreach ($vote->getSections() as $section) {
                /** @var Section $section */
                $maxChoice = null;
                $maxResult = -1;
                foreach ($section->getChoices() as $choice) {
                    /** @var Choice $choice */
                    if ($choice->getResult() > $maxResult) {
                        $maxChoice = $choice;
                        $maxResult = $choice->getResult();
                    }
                }
                $section->win = $maxChoice;
            }
            return array_values(array_map(function($section) {
                /** @var Section $section */
                $info = $section->jsonSerialize();
                $info["maxChoice"] = [
                    "name" => $section->win->getName(),
                    "result" => $section->win->getResult()
                ];
                return $info;
            }, $vote->getSections()->toArray()));
        } else {
            return $vote->getSections();
        }
    }

    public function listAll() {
        $votes = $this->objectManager->getRepository(Vote::class)->findAll();
        return $votes;
    }

    public function findCurrent(): ?Vote {
        $votes = $this->objectManager->getRepository(Vote::class)->findEnabled();
        if(count($votes) == 1)
            return $votes[0];
        else
            return null;
    }


}