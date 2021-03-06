<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, \JsonSerializable
{

    const ROLE_USER = "ROLE_USER";

    public function __construct(string $phone, string $identifier, int $role)
    {
        $this->phone = $phone;
        $this->identifier = $identifier;
        $this->candidates = new ArrayCollection();
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $phone;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $identifier;

    /**
     * @return int|null
     */

    /**
     * If a user is a candidate in a vote.
     *
     * @ORM\ManyToMany(targetEntity="Choice", inversedBy="users")
     * @ORM\JoinTable(name="user_candidates")
     */
     private $candidates;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return [self::ROLE_USER];
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        return null;
    }

    public function getIdentifier() {
        return $this->identifier;
    }

    public function addCandidate(Choice $choice) {
        if(!$this->candidates->contains($choice))
            $this->candidates->add($choice);
        $choice->addUser($this);
    }

    public function removeCandidate(Choice $choice) {
        if($this->candidates->contains($choice))
            $this->candidates->removeElement($choice);
        $choice->removeUser($this);
    }


    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "name" => $this->getName(),
            "phone" => $this->getPhone(),
            "identifier" => $this->identifier
        ];
    }
}
