<?php

namespace App\Entity;

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
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @JMS\SerializedName("id")
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
     * @ORM\Column(type="integer")
     */
    private $sequence;

    /**
     * @ORM\ManyToMany(targetEntity="Channel", mappedBy="categories", cascade={"persist"})
     * @ORM\JoinTable(name="category_channel")
     * @ORM\OrderBy({"popularity" = "ASC"})
     */
    private $channels;

    public function __construct($entityId, $title, $type, $sequence)
    {
        $this->entityId = $entityId;
        $this->title = $title;
        $this->type = $type;
        $this->sequence = $sequence;
        $this->channels = new ArrayCollection();
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

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setChannels(array $channels): self
    {
        $this->channels = $channels;

        return $this;
    }

    public function getChannels()
    {
        return $this->channels;
    }
}
