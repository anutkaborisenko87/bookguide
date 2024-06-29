<?php

namespace App\Service;

use App\Model\AuthorsListResponse;

interface AuthorsServiceInterface
{
    public function getAuthors(int $page, ?int $limit): AuthorsListResponse;
}
