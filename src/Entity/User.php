<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[
    ORM\Table('user'),
    ORM\Entity(repositoryClass: 'App\Repository\UserRepository'),
    UniqueEntity(fields: 'email', message: 'Cet adresse email est déjà pris.', groups: ['create','edit']),
    UniqueEntity(fields: 'username', message: 'Ce nom d\'utilisateur est déjà pris.', groups: ['create','edit'])
]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[
        ORM\Id,
        ORM\Column(type: "integer"),
        ORM\GeneratedValue( strategy: 'AUTO')
    ]
    private int $id;

    #[
        ORM\Column(type: 'string', length: 25, unique: true),
        Assert\NotBlank(message: 'Vous devez saisir un nom d\'utilisateur.', groups: ['create','edit']),
    ]
    private ?string $username;

    #[
        ORM\Column(type: 'string', length: 64),
        Assert\NotBlank(message: 'Vous devez saisir un mot de passe valide.', groups: ['create'])
    ]
    private string $password;

    #[
        ORM\Column(type: 'string', length: 60, unique: true),
        Assert\NotBlank(message: 'Vous devez saisir un adresse email.', groups: ['create','edit']),
        Assert\Email(message: 'Le format de l\'adresse n\'est pas correcte.', groups: ['create','edit']),
    ]
    private ?string $email;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Task::class)]
    private $tasks;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at;

    #[
        ORM\Column(type: 'json'),
        Assert\NotBlank(message: 'Le choix d\'un rôle est requis', groups: ['create', 'edit'])
    ]
    private array $roles = [];


    #[Pure] public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }


    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Returns the identifier for this user (e.g. its username or email address).
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getSalt()
    {
        return null;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}
