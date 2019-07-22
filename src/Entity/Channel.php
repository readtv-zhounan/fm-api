<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation as JMS;

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
     * @JMS\SerializedName("id")
     * @JMS\Groups({"home"})
     */
    private $entityId;

    /**
     * @ORM\Column(type="integer")
     * @JMS\SerializedName("popularity")
     * @JMS\Groups({"home"})
     */
    private $popularity;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"home"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @JMS\Groups({"home"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"home"})
     */
    private $updateTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"home"})
     */
    private $smallThumb;

    /**
     * @ORM\OneToMany(targetEntity="CategoryChannel", mappedBy="channel", cascade={"persist"})
     */
    private $categoryChannels;

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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description): self
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
            $categoryChannels->setChannel($this);
        }

        return $this;
    }

    public function removeCategoryChannels(CategoryChannel $categoryChannels): self
    {
        if ($this->categoryChannels->contains($categoryChannels)) {
            $this->categoryChannels->removeElement($categoryChannels);
            // set the owning side to null (unless already changed)
            if ($categoryChannels->getChannel() === $this) {
                $categoryChannels->setChannel(null);
            }
        }

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"home"})
     * @JMS\SerializedName("video_url")
     */
    public function getVideoUrl()
    {
        return $_ENV['API_VIDEO_HOST'].'/live/'.$this->getEntityId().'/64k.mp3';
    }
}
