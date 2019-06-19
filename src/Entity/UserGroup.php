<?php

namespace App\Entity;

use App\Interfaces\ApiableInterface;
use App\Interfaces\CmsableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserGroupRepository")
 */
class UserGroup implements CmsableInterface, ApiableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="userGroup")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function __toString() {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setUserGroup($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getUserGroup() === $this) {
                $user->setUserGroup(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function toCmsTable(): Array
    {
        return [
            'title' => [
                'text' => $this->getTitle(),
                'link' => '/user/group/edit/' . $this->getSlug(),
            ],
        ];
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->getId(),
            'slug' => $this->getSlug(),
            'title' => $this->getTitle()
        ];
    }
}
