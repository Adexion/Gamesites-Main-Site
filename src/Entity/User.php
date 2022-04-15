<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email"}, message="Konto o podanym adresie email już istnieje.")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="client")
     */
    private $application;

    /**
     * @ORM\OneToOne(targetEntity=Address::class, inversedBy="client", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="boolean", options={"default":false})
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="client")
     */
    private $invoice;

    /**
     * @ORM\ManyToMany(targetEntity=Workspace::class, inversedBy="users")
     */
    private $workspace;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $forceChangePassword = false;

    /**
     * @ORM\OneToMany(targetEntity=Ticket::class, mappedBy="creator")
     */
    private $tickets;

    /**
     * @ORM\ManyToOne(targetEntity=Company::class, inversedBy="users")
     */
    private $company;

    /**
     * @ORM\OneToOne(targetEntity=Agreements::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $agreements;

    /**
     * @ORM\OneToOne(targetEntity=UserReferrer::class, mappedBy="client", cascade={"persist", "remove"})
     */
    private $referrer;

    /**
     * @ORM\ManyToOne(targetEntity=UserReferrer::class, inversedBy="invited")
     */
    private $inviting;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @ORM\Version
     */
    private $registrationDate;

    /**
     * @ORM\OneToMany(targetEntity=ReferrerPoint::class, mappedBy="client", orphanRemoval=true)
     */
    private $referrerPoints;

    public function __construct()
    {
        $this->application = new ArrayCollection();
        $this->invoice = new ArrayCollection();
        $this->workspace = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->referrerPoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    public function getUsername(): string
    {
        return (string)$this->email;
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

    public function toggleAdmin()
    {
        if (($roles = array_search("ROLE_ADMIN", $this->roles)) !== false) {
            unset($this->roles[$roles]);
        } else {
            $this->roles[] = "ROLE_ADMIN";
        }
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        if (!$password) {
            return $this;
        }

        $this->password = $password;

        return $this;
    }

    public function getApplication(): Collection
    {
        return $this->application;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->application->contains($application)) {
            $this->application[] = $application;
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        $this->application->removeElement($application);

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {

    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoice(): Collection
    {
        return $this->invoice;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoice->contains($invoice)) {
            $this->invoice[] = $invoice;
            $invoice->setClient($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoice->removeElement($invoice)) {
            if ($invoice->getClient() === $this) {
                $invoice->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Workspace[]
     */
    public function getWorkspace(): Collection
    {
        return $this->workspace;
    }

    public function addWorkspace(Workspace $workspace): self
    {
        if (!$this->workspace->contains($workspace)) {
            $this->workspace[] = $workspace;
        }

        return $this;
    }

    public function removeWorkspace(Workspace $workspace): self
    {
        $this->workspace->removeElement($workspace);

        return $this;
    }

    public function getForceChangePassword(): ?bool
    {
        return $this->forceChangePassword;
    }

    public function setForceChangePassword(?bool $forceChangePassword): self
    {
        $this->forceChangePassword = $forceChangePassword;

        return $this;
    }

    /**
     * @return Collection|Ticket[]
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): self
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets[] = $ticket;
            $ticket->setCreator($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): self
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getCreator() === $this) {
                $ticket->setCreator(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getAgreements(): ?Agreements
    {
        return $this->agreements;
    }

    public function setAgreements(Agreements $agreements): self
    {
        $this->agreements = $agreements;

        return $this;
    }

    public function getReferrer(): ?UserReferrer
    {
        return $this->referrer;
    }

    public function setReferrer(UserReferrer $referrer): self
    {
        // set the owning side of the relation if necessary
        if ($referrer->getClient() !== $this) {
            $referrer->setClient($this);
        }

        $this->referrer = $referrer;

        return $this;
    }

    public function getInviting(): ?UserReferrer
    {
        return $this->inviting;
    }

    public function setInviting(?UserReferrer $inviting): self
    {
        $this->inviting = $inviting;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @return Collection<int, ReferrerPoint>
     */
    public function getReferrerPoints(): Collection
    {
        return $this->referrerPoints;
    }

    public function addReferrerPoint(ReferrerPoint $referrerPoint): self
    {
        if (!$this->referrerPoints->contains($referrerPoint)) {
            $this->referrerPoints[] = $referrerPoint;
            $referrerPoint->setClient($this);
        }

        return $this;
    }

    public function removeReferrerPoint(ReferrerPoint $referrerPoint): self
    {
        if ($this->referrerPoints->removeElement($referrerPoint)) {
            // set the owning side to null (unless already changed)
            if ($referrerPoint->getClient() === $this) {
                $referrerPoint->setClient(null);
            }
        }

        return $this;
    }
}
