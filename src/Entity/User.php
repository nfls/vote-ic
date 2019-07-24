<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{

    const ROLE_ANONYMOUS = 0;
    const ROLE_VOTER = 1;
    const ROLE_PARTICIPANTS = 2;
    const ROLE_ADMIN = 3;
    const ROLE_ROOT = 4;

    public function __construct(string $phone, string $identifier, int $role)
    {
        $this->phone = $phone;
        $this->identifier = $identifier;
        $this->role = $role;
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
     * @ORM\Column(type="integer")
     */

    private $role;
    /**
     * @return int|null
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    static function getAnonymousUser() {
        return new User("", "", self::ROLE_ANONYMOUS);
    }
}
