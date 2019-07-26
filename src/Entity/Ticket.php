<?php
namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\DateTime;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 */
class Ticket implements \JsonSerializable
{
    /**
     * @var Uuid
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;
    /**
     * @var Vote
     *
     * @ORM\ManyToOne(targetEntity="Vote")
     */
    private $vote;
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;
    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Choice")
     * @ORM\JoinTable(name="ticket_choices")
     */
    private $choices;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $userAgent;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $deviceId;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $ip;
    /**
     * @var string
     *
     * @ORM\Column(type="json")
     */
    private $other;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $code;
    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    public function  __construct(Vote $vote, User $user, $input, string $ip, string $userAgent, string $deviceId, array $other)
    {
        if(!is_array($input))
            throw new \InvalidArgumentException("表格信息不完整。");

        $choices = [];

        foreach($vote->getSections() as $section) {
            /** @var $section Section */
            if (!array_key_exists($section->getId(), $input))
                throw new \InvalidArgumentException("表格信息不完整。");
            $choiceId = $input[$section->getId()];
            if (is_null($choiceId))
                throw new \InvalidArgumentException("表格信息不完整。");
            $userChoice = array_values(array_filter($section->getChoices()->toArray(), function($choice) use($choiceId) {
                /** @var $choice Choice */
                return $choice->getId() == $choiceId;
            }));
            if (count($userChoice) != 1)
                throw new \InvalidArgumentException("表格信息不完整。");
            array_push($choices, $userChoice[0]);
        }

        $this->vote = $vote;
        $this->user = $user;
        $this->choices = $choices;
        $this->code = bin2hex(random_bytes(4));
        $this->time = new \DateTime();
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->deviceId = $deviceId;
        $this->other = $other;
    }

    public function getId()
    {
        return $this->id;
    }
    /**
     * @return ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }
    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    public function jsonSerialize()
    {
        return [
            "choices" => $this->getChoices()->toArray(),
            "code" => $this->getCode()
        ];
    }
}