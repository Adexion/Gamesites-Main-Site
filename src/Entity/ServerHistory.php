<?php

namespace App\Entity;

use App\Repository\ServerHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServerHistoryRepository::class)
 */
class ServerHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Server::class, mappedBy="history")
     */
    private $server;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiryDate;

    /**
     * @ORM\OneToOne(targetEntity=Invoice::class, cascade={"persist", "remove"})
     */
    private $invoice;

    public function __construct()
    {
        $this->server = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Server[]
     */
    public function getServer(): Collection
    {
        return $this->server;
    }

    public function addServer(Server $server): self
    {
        if (!$this->server->contains($server)) {
            $this->server[] = $server;
            $server->setHistory($this);
        }

        return $this;
    }

    public function removeServer(Server $server): self
    {
        if ($this->server->removeElement($server)) {
            // set the owning side to null (unless already changed)
            if ($server->getHistory() === $this) {
                $server->setHistory(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
}
