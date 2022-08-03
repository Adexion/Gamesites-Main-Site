<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
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
     * @ORM\Column(type="string")
     */
    private $expiryDate;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isActive;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $invoice;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $paymentNotification;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $creator;

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

    public function getExpiryDate(): ?string
    {
        return $this->expiryDate;
    }

    public function setExpiryDate(?string $expiryDate): self
    {
        $this->expiryDate = $expiryDate;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function setInvoice(?bool $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function getInvoice(): ?bool
    {
        return $this->invoice;
    }

    public function getPaymentNotification(): ?bool
    {
        return $this->paymentNotification;
    }

    public function setPaymentNotification(bool $paymentNotification): self
    {
        $this->paymentNotification = $paymentNotification;

        return $this;
    }

    public function getCreator(): ?UserInterface
    {
        return $this->creator;
    }

    public function setCreator(?UserInterface $creator): self
    {
        $this->creator = $creator;

        return $this;
    }
}
