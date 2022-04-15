<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Enum\ReferrerPointType;
use App\Repository\UserReferrerRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserReferrerRepository::class)
 */
class UserReferrer
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
    private $code;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="referrer", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="inviting")
     */
    private $invited;

    /**
     * @ORM\ManyToMany(targetEntity=ReferrerPoint::class)
     */
    private $point;

    public function __construct()
    {
        $this->invited = new ArrayCollection();
        $this->point = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(UserInterface $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getInvited(): Collection
    {


        return $this->invited;
    }

    public function addInvited(UserInterface $invited): self
    {
        if (!$this->invited->contains($invited)) {
            $this->invited[] = $invited;
            $invited->setInviting($this);
        }

        return $this;
    }

    public function removeInvited(User $invited): self
    {
        if ($this->invited->removeElement($invited)) {
            // set the owning side to null (unless already changed)
            if ($invited->getInviting() === $this) {
                $invited->setInviting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReferrerPoint>
     */
    public function getPoint(): Collection
    {
        return $this->point;
    }

    public function addPoint(ReferrerPoint $point): self
    {
        if (!$this->point->contains($point)) {
            $this->point[] = $point;
        }

        return $this;
    }

    public function removePoint(ReferrerPoint $point): self
    {
        $this->point->removeElement($point);

        return $this;
    }

    public function getPointCount(): int
    {
        /** @var ReferrerPoint $point */
        $count = 0;
        foreach ($this->point as $point) {
            $count += $point->getPoint();
        }

        return $count;
    }

    public function getSpends(): array
    {
        return array_filter(array_map(function (ReferrerPoint $point) {
            if ($point->getType() !== ReferrerPointType::SPENDS) {
               return null;
            }

            return $point;
        }, $this->getPoint()->toArray()));
    }
}
