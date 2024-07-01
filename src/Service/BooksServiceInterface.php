<?php

namespace App\Service;

use App\Model\BookListResponse;

interface BooksServiceInterface
{
    public function getAllBooks(int $page, ?int $limit): BookListResponse;
}
