<?php

namespace App\Entity;

use App\Interfaces\ApiableInterface;
use App\Interfaces\CmsableInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements CmsableInterface, ApiableInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    private $slug;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserGroup", inversedBy="users")
     */
    private $userGroup;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

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

    public function getUserGroup(): ?UserGroup
    {
        return $this->userGroup;
    }

    public function hasUserGroup(): bool
    {
        return !empty($this->userGroup);
    }

    public function setUserGroup(?UserGroup $userGroup): self
    {
        $this->userGroup = $userGroup;

        return $this;
    }

    public function getGroupTitle($format = '%s'): string
    {
        $userGroup = $this->getUserGroup();

        if (!($userGroup instanceof UserGroup)) {
            return '';
        }

        return sprintf($format, $this->getUserGroup()->getTitle());
    }

    public function toCmsTable(): Array
    {
        return [
            'name' => [
                'text' => $this->getName(),
                'link' => '/user/edit/' . $this->getSlug(),
            ],
            'userGroup' => [
                'text' => $this->getGroupTitle(),
            ]
        ];
    }

    public function toApiResponse(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'userGroup' => $this->hasUserGroup() ? $this->getUserGroup()->toApiResponse() : null,
        ];
    }
}
