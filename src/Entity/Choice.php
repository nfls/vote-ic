<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChoiceRepository")
 */
class Choice implements \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="Section", inversedBy="choices")
     * @ORM\JoinColumn(name="section_id", referencedColumnName="id")
     */
    private $section;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $adjust = 0;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $count = 0;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="candidates")
     */
    private $users;


    public function __construct()
    {
        $this->users = new ArrayCollection();
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
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return int
     */
    public function getAdjust(): int
    {
        return $this->adjust;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section): void
    {
        $this->section = $section;
    }

    public function addUser(User $user) {
        if(!$this->users->contains($user))
            $this->users->add($user);
    }

    public function removeUser(User $user) {
        if($this->users->contains($user))
            $this->users->removeElement($user);
    }

    public function resetCount() {
        $this->count = 0;
    }

    public function addCount() {
        $this->count ++;
    }

    public function getCount() {
        return $this->count;
    }

    public function getResult() {
        return $this->count + $this->adjust;
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName()
        ];
    }
}
