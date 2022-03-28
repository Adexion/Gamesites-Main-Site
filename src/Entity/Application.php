<?php

namespace App\Entity;

use App\Repository\ApplicationRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ApplicationRepository::class)
 * @UniqueEntity(fields={"name"}, message="There is already an application with this name")
 */
class Application
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
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiryDate;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $wasInstallerRun = false;

    /**
     * @ORM\ManyToOne(targetEntity=ApplicationHistory::class, inversedBy="application")
     */
    private $history;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="application")
     */
    private $invoices;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $installationFinish = false;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $invoice = false;

    /**
     * @ORM\ManyToOne(targetEntity=Workspace::class, inversedBy="application")
     */
    private $workspace;

    public function __construct()
    {
        $this->client = new ArrayCollection();
        $this->invoices = new ArrayCollection();
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

    public function getExpiryDate(): ?DateTimeInterface
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(DateTimeInterface $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getHistory(): ?ApplicationHistory
    {
        return $this->history;
    }

    public function setHistory(?ApplicationHistory $history): self
    {
        $this->history = $history;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setApplication($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            if ($invoice->getApplication() === $this) {
                $invoice->setApplication(null);
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

    public function getInstallationFinish(): ?bool
    {
        return $this->installationFinish;
    }

    public function setInstallationFinish(bool $installationFinish): self
    {
        $this->installationFinish = $installationFinish;

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

    public function getInvoice(): ?bool
    {
        return $this->invoice;
    }

    public function setInvoice(bool $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'domain' => $this->domain,
            'expiryDate' => $this->expiryDate,
            'installationFinish' => $this->installationFinish,
            'workspace' => $this->workspace,
            'coupon' => $this->coupon
        ];
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
