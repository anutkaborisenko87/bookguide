<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Model\BookAuthorsListItem;
use App\Model\BookListItem;
use App\Model\BookListResponse;
use App\Repository\BookRepository;

class BooksService implements BooksServiceInterface
{
    private $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    final public function getAllBooks(int $page, ?int $limit = 10): BookListResponse
    {
        $offset = ($page - 1) * $limit;
        $totalAuthors = $this->bookRepository->count([]);
        $totalPages = ceil((float)$totalAuthors / $limit);
        $authors = $this->bookRepository->findBy([], null, $limit, $offset);
        $items = array_map(function (Book $book) {
            $authors = array_map(fn(Author $author) => new BookAuthorsListItem(
                $author->getId(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPatronomicName()
            ), $book->getAuthors()->toArray()
            );

            return new BookListItem(
                $book->getId(),
                $book->getTitle(),
                $book->getDescription(),
                $book->getImage(),
                $book->getPublishedAt(),
                $authors
            );
        }, $authors);
        return new BookListResponse($items, $page, $totalPages, $limit);
    }
}
