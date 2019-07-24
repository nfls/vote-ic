<?php
namespace App\Entity;
use App\Library\VoteStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
/**
 * @ORM\Entity(repositoryClass="App\Repository\VoteRepository")
 */
class Vote implements \JsonSerializable
{
    /**
     * @var Uuid
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $title;
    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Section", mappedBy="vote")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $sections;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $status = VoteStatus::HIDDEN;

    const CRAFT = 0;


    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?? "";
    }
    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content ?? "";
    }
    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function addSection(Section $section) {
        $this->sections->add($section);
        $section->setVote($this);
    }

    public function getSections() {
        return $this->sections;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "content" => $this->getContent(),
            "sections" => $this->getSections()->toArray(),
            "status" => $this->getStatus()
        ];
    }
}