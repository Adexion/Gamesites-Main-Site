<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ServerRepository::class)
 * @UniqueEntity(fields={"name"}, message="There is already an application with this name")
 */
class Server
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
    private $coupon;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     * @Assert\Unique
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="server")
     */
    private $client;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $wasInstallerRun = false;

    /**
     * @ORM\ManyToOne(targetEntity=ServerHistory::class, inversedBy="server")
     */
    private $history;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="server")
     */
    private $invoice;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $instalationFinish = false;

    /**
     * @ORM\OneToOne(targetEntity=Workspace::class, inversedBy="server", cascade={"persist", "remove"})
     */
    private $workspace;

    public function __construct()
    {
        $this->client = new ArrayCollection();
        $this->invoice = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoupon(): ?string
    {
        return $this->coupon;
    }

    public function setCoupon(string $coupon): self
    {
        $this->coupon = $coupon;

        return $this;
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

    public function getDir(): ?string
    {
        return lcfirst(join('', array_map(fn($value) => ucfirst(strtolower($value)), explode(' ', $this->getName()))));
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getClient(): Collection
    {
        return $this->client;
    }

    public function addClient(User $client): self
    {
        if (!$this->client->contains($client)) {
            $this->client[] = $client;
            $client->addServer($this);
        }

        return $this;
    }

    public function removeClient(User $client): self
    {
        if ($this->client->removeElement($client)) {
            $client->removeServer($this);
        }

        return $this;
    }

    public function getExpiryDate(): ?\DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(\DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getHistory(): ?ServerHistory
    {
        return $this->history;
    }

    public function setHistory(?ServerHistory $history): self
    {
        $this->history = $history;

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
            $invoice->setServer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoice->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getServer() === $this) {
                $invoice->setServer(null);
            }
        }

        return $this;
    }

    public function setWasInstallerRun(?bool $wasInstallerRun): self
    {
        $this->wasInstallerRun = $wasInstallerRun;

        return $this;
    }

    public function getWasInstallerRun(): ?bool
    {
        return $this->wasInstallerRun;
    }

    public function getInstalationFinish(): ?bool
    {
        return $this->instalationFinish;
    }

    public function setInstalationFinish(bool $instalationFinish): self
    {
        $this->instalationFinish = $instalationFinish;

        return $this;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(?Workspace $workspace): self
    {
        $this->workspace = $workspace;

        return $this;
    }
}
