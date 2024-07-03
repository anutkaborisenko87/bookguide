<?php

namespace App\Model;

class AuthorListItem
{
    private ?int $id;
    private string $first_name;
    private string $last_name;
    private ?string $patronomic_name;
    private array $books;

    public function __construct(?int $id,
                                string $first_name,
                                string $last_name,
                                ?string $patronomic_name = null,
                                ?array $books = []
    )
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->patronomic_name = $patronomic_name;
        $this->books = $books;
    }

    public function getBooks(): array
    {
        return $this->books;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function getPatronomicName(): ?string
    {
        return $this->patronomic_name;
    }

}
