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
     *      "1. 主席": ["Leigh Smith", "Leigh Smith"],
     *      "2. 代表": ["Walter Nagles", "Walter Nagles"]
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