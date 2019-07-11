<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ChannelRepository")
 */
class Channel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $entityId;

    /**
     * @ORM\Column(type="integer")
     */
    private $popularity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateTime;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $smallThumb;

    /**
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="channels", cascade={"persist"})
     * @ORM\JoinTable(name="category_channel")
     */
    private $categories;

    public function __construct(
        $entityId,
        $popularity,
        $title,
        $description,
        $updateTime,
        $smallThumb
    ) {
        $this->entityId = $entityId;
        $this->popularity = $popularity;
        $this->title = $title;
        $this->description = $description;
        $this->updateTime = $updateTime;
        $this->smallThumb = $smallThumb;

        $this->categories = new ArrayCollection();
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

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(int $popularity): self
    {
        $this->popularity = $popularity;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function setUpdateTime(\DateTimeInterface $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    public function getSmallThumb(): ?string
    {
        return $this->smallThumb;
    }

    public function setSmallThumb(string $smallThumb): self
    {
        $this->smallThumb = $smallThumb;

        return $this;
    }

    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory($category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            // $product->setCategory($this);
        }

        return $this;
    }
}
