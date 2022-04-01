<?php

namespace App\Entity;

use App\Repository\AgreementsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgreementsRepository::class)
 */
class Agreements
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $marketing = true;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private $rodo = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarketing(): ?bool
    {
        return $this->marketing;
    }

    public function setMarketing(bool $marketing): self
    {
        $this->marketing = $marketing;

        return $this;
    }

    public function getRodo(): ?bool
    {
        return $this->rodo;
    }

    public function setRodo(bool $rodo): self
    {
        $this->rodo = $rodo;

        return $this;
    }
}
