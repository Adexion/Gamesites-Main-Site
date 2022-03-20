<?php

namespace App\Entity;

use App\Repository\WorkspaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=WorkspaceRepository::class)
 */
class Workspace
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToOne(targetEntity=Application::class, mappedBy="workspace", cascade={"persist", "remove"})
     */
    private $application;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="workspace")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }


    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        if ($application === null && $this->application !== null) {
            $this->application->setWorkspace(null);
        }

        if ($application !== null && $application->getWorkspace() !== $this) {
            $application->setWorkspace($this);
        }

        $this->application = $application;

        return $this;
    }

    /**
     * @return Collection|UserInterface[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(UserInterface $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addWorkspace($this);
        }

        return $this;
    }

    public function removeUser(UserInterface $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeWorkspace($this);
        }

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?UserInterface $creator): self
    {
        $this->creator = $creator;

        return $this;
    }
}
