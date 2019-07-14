<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @JMS\SerializedName("id")
     * @JMS\Groups({"home"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Groups({"home"})
     */
    private $entityId;

    /**
     * @ORM\Column(type="string", length=60)
     * @JMS\SerializedName("title")
     * @JMS\Groups({"home"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     */
    private $sequence;

    /**
     * @ORM\OneToMany(targetEntity="CategoryChannel", mappedBy="category", cascade={"persist"})
     */
    private $categoryChannels;

    public function __construct($entityId, $title, $type, $sequence)
    {
        $this->entityId = $entityId;
        $this->title = $title;
        $this->type = $type;
        $this->sequence = $sequence;

        $this->categoryChannels = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function setCategoryChannels(array $categoryChannels): self
    {
        $this->categoryChannels = $categoryChannels;

        return $this;
    }

    /**
     * @return Collection|CategoryChannel[]
     */
    public function getCategoryChannels(): Collection
    {
        return $this->categoryChannels;
    }

    public function addCategoryChannels(CategoryChannel $categoryChannels): self
    {
        if (!$this->categoryChannels->contains($categoryChannels)) {
            $this->categoryChannels[] = $categoryChannels;
            $categoryChannels->setCategory($this);
        }

        return $this;
    }

    public function removeCategoryChannels(CategoryChannel $categoryChannels): self
    {
        if ($this->categoryChannels->contains($categoryChannels)) {
            $this->categoryChannels->removeElement($categoryChannels);
            // set the owning side to null (unless already changed)
            if ($categoryChannels->getCategory() === $this) {
                $categoryChannels->setCategory(null);
            }
        }

        return $this;
    }
}
