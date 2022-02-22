<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @codeCoverageIgnore
 */
#[ORM\Entity(repositoryClass: 'App\Repository\TaskRepository')]
#[ORM\Table(name: 'task')]
class Task
{

    #[
        ORM\Column(type: 'integer'),
        ORM\Id,
        ORM\GeneratedValue(strategy: 'AUTO')
    ]
    private int $id;

    #[ORM\Column(type: 'datetime')]
    private \Datetime $createdAt;

    #[
        ORM\Column(type: 'string'),
        Assert\NotBlank(message: 'Vous devez saisir un titre.', groups: ['create', 'edit'])
    ]
    private ?string $title;

    #[
        ORM\Column(type: 'text'),
        Assert\NotBlank(message: 'Vous devez saisir du contenu.', groups: ['create', 'edit'])
    ]
    private ?string $content;

    #[ORM\Column(type: 'boolean')]
    private bool $isDone;

    #[
        ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tasks'),
        ORM\JoinColumn(nullable: false)
    ]
    private ?User $user;

    public function __construct()
    {
        $this->createdAt = new \Datetime();
        $this->isDone = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \Datetime
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    public function toggle($flag)
    {
        $this->isDone = $flag;
    }

    public function getIsDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
