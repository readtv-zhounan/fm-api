<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryChannelRepository")
 */
class CategoryChannel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="categoryChannels", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="Channel", inversedBy="categoryChannels", cascade={"persist"})
     * @ORM\JoinColumn(name="channel_id")
     */
    private $channel;

    /**
     * @ORM\Column(type="integer")
     */
    private $sequence;

    public function __construct(
        Category $category,
        Channel $channel,
        $sequence
    ) {
        $this->category = $category;
        $this->channel = $channel;
        $this->sequence = $sequence;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

}
