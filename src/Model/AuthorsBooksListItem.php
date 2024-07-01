<?php

namespace App\Model;

class AuthorsBooksListItem
{
    private int $id;
    private string $title;
    private ?string $image;

    public function __construct(int $id, string $title, ?string $image)
    {
        $this->id = $id;
        $this->title = $title;
        $this->image = $image;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
}
