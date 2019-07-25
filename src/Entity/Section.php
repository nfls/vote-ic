<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SectionRepository")
 */
class Section implements \JsonSerializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Vote", inversedBy="sections")
     * @ORM\JoinColumn(name="vote_id", referencedColumnName="id")
     */
    private $vote;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Choice", mappedBy="section")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $choices;

    /**
     * @var Choice
     */
    public $win = null;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * @param mixed $vote
     */
    public function setVote($vote): void
    {
        $this->vote = $vote;
    }

    /**
     * @return mixed
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param $choice
     */
    public function addChoice(Choice $choice): void
    {
        $this->choices->add($choice);
        $choice->setSection($this);
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "choices" => $this->choices->toArray()
        ];
    }
}
