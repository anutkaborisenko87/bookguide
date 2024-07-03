<?php

namespace App\Model;

use DateTime;

class BookDetails
{
    private ?int $id;
    private ?string $title;
    private ?string $description;
    private ?string $image;
    private ?string $published_at;
    private array $authors;

    public function __construct(?int $id,
                                ?string $title,
                                ?string $description = null,
                                ?string $image = null,
                                ?DateTime $published_at = null,
                                ?array $authors = []
    )
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->published_at = $published_at ? $published_at->format('Y-m-d') : null;
        $this->authors = $authors;
    }

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getPublishedAt(): ?string
    {
        return $this->published_at;
    }

}
