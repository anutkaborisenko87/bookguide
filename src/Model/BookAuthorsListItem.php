<?php

namespace App\Model;

class BookAuthorsListItem
{
    private int $id;
    private string $first_name;
    private string $last_name;
    private ?string $patronomic_name;

    public function __construct(int $id,
                                string $first_name,
                                string $last_name,
                                ?string $patronomic_name = null
    )
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->patronomic_name = $patronomic_name;
    }

    public function getId(): int
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
