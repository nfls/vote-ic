<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Validator\Constraints\DateTime;
/**
 * @ORM\Entity(repositoryClass="App\Repository\TicketRepository")
 */
class Ticket
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
     * @var array
     *
     * @ORM\Column(type="json")
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
     * @ORM\Column(type="text")
     */
    private $code;
    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    public function  __construct(Vote $vote, User $user, $choices, string $ip, string $userAgent, string $deviceId)
    {
        if(!is_array($choices) || count($vote->getOptions()) != count($choices))
            throw new \InvalidArgumentException("Invalid ticket.");
        for($i=0; $i<count($vote->getOptions()); $i++) {
            if(!is_int($choices[$i]) || $choices[$i] < 0 || $choices[$i] >= count($vote->getOptions()[$i]["options"]))
                throw new \InvalidArgumentException("Invalid ticket for ".$i.".");
        }
        $this->vote = $vote;
        $this->user = $user;
        $this->choices = $choices;
        $this->code = bin2hex(random_bytes(32));
        $this->time = new \DateTime();
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->deviceId = $deviceId;
    }
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return array
     */
    public function getChoices(): array
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
}