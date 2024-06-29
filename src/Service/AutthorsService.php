<?php

namespace App\Service;

use App\Entity\Author;
use App\Model\AuthorListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;

class AutthorsService implements AuthorsServiceInterface
{
    private $authorRepository;
    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository = $authorRepository;
    }

    final public function getAuthors(int $page, ?int $limit = 10): AuthorsListResponse
    {
        $offset = ($page - 1) * $limit;
        $totalAuthors = $this->authorRepository->count([]); // counts total authors
        $totalPages = ceil((float)$totalAuthors / $limit);
        $authors = $this->authorRepository->findBy([], null, $limit, $offset);
        $items = array_map(fn(Author $author) => new AuthorListItem(
            $author->getId(), $author->getFirstName(), $author->getLastName(), $author->getPatronomicName()
        ), $authors);
        return new AuthorsListResponse($items, $page, $totalPages, $limit);
    }
}
