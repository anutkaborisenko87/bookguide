<?php

namespace App\Model;

class FoundedBookListResponse
{
    private $books;

    public function __construct(array $books)
    {
        $this->books = $books;

    }

    /**
     * @return BookDetails[]
     */
    final public function getBooks(): array
    {
        return $this->books;
    }
}
