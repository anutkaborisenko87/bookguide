<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Exception\ValidationException;
use App\Model\AuthorListItem;
use App\Model\AuthorsBooksListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AutthorsService implements AuthorsServiceInterface
{
    private ValidatorInterface $validator;
    private AuthorRepository $authorRepository;
    public function __construct(AuthorRepository $authorRepository, ValidatorInterface $validator)
    {
        $this->authorRepository = $authorRepository;
        $this->validator = $validator;
    }

    final public function getAuthors(int $page, ?int $limit = 10): AuthorsListResponse
    {
        $offset = ($page - 1) * $limit;
        $totalAuthors = $this->authorRepository->count([]);
        $totalPages = ceil((float)$totalAuthors / $limit);
        $authors = $this->authorRepository->findBy([], null, $limit, $offset);
        $items = array_map(function (Author $author) {
            $books = array_map(
                fn(Book $book) => new AuthorsBooksListItem(
                    $book->getId(),
                    $book->getTitle(),
                    $book->getImage()
                ),
                $author->getBooks()->toArray()
            );

            return new AuthorListItem(
                $author->getId(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPatronomicName(),
                $books
            );
        }, $authors);
        return new AuthorsListResponse($items, $page, $totalPages, $limit);
    }

    final public function createAuthor(
        string $firstName,
        string $lastName,
        ?string $patronomicName = null
    ): AuthorListItem
    {
        $author = new Author();
        $author->setFirstName($firstName);
        $author->setLastName($lastName);
        if ($patronomicName) {
            $author->setPatronomicName($patronomicName);
        }
        $errors = $this->validator->validate($author);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            throw new ValidationException($errorMessages);
        }
        $this->authorRepository->add($author, true);
            return new AuthorListItem(
                $author->getId(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPatronomicName()
            );
    }
}
