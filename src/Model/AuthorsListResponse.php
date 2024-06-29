<?php

namespace App\Model;

class AuthorsListResponse
{
    private $items;
    private $currentPage;
    private $totalPages;
    private $itemsPerPage;

    public function __construct(array $items, int $currentPage, int $totalPages, int $itemsPerPage)
    {
        $this->items = $items;
        $this->currentPage = $currentPage;
        $this->totalPages = $totalPages;
        $this->itemsPerPage = $itemsPerPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * @return AuthorListItem[]
     */
    final public function getItems(): array
    {
        return $this->items;
    }
}
