<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Model\BookAuthorsListItem;
use App\Model\BookDetails;
use App\Model\BookListResponse;
use App\Model\FoundedBookListResponse;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Exception\ValidationException;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BooksService implements BooksServiceInterface
{
    private BookRepository $bookRepository;
    private ValidatorInterface $validator;
    private AuthorRepository $authorRepository;
    private Filesystem $filesystem;
    private string $uploadsDirectory;

    public function __construct(BookRepository     $bookRepository,
                                ValidatorInterface $validator,
                                AuthorRepository   $authorRepository,
                                Filesystem         $filesystem,
                                string             $uploadsDirectory
    )
    {
        $this->bookRepository = $bookRepository;
        $this->validator = $validator;
        $this->authorRepository = $authorRepository;
        $this->filesystem = $filesystem;
        $this->uploadsDirectory = $uploadsDirectory;
    }

    final public function getAllBooks(int $page, ?int $limit = 10): BookListResponse
    {
        $offset = ($page - 1) * $limit;
        $totalAuthors = $this->bookRepository->count([]);
        $totalPages = ceil((float)$totalAuthors / $limit);
        $books = $this->bookRepository->findBy([], null, $limit, $offset);
        $items = array_map(function (Book $book) {
            return $this->getBookMapping($book);
        }, $books);
        return new BookListResponse($items, $page, $totalPages, $limit);
    }

    final public function getBooksByAuthorName(?string $name): FoundedBookListResponse
    {
        if (is_null($name)) {
            throw new ValidationException(['authorName' => 'Parameter authorName is missing from request.'], 'Validation error');
        }
        $authors = $this->authorRepository->findByPartialName($name);
        $books = [];
        foreach ($authors as $author) {
            foreach ($author->getBooks() as $book) {
                $books[] = $this->getBookMapping($book);
            }
        }
        return new FoundedBookListResponse($books);
    }

    final public function getBookById(int $id): BookDetails
    {
        $book = $this->bookRepository->getById($id);

        return $this->getBookMapping($book);
    }

    final public function createBook(
        string        $title,
        string        $description,
        array         $authorIds,
        ?UploadedFile $imageFile,
        ?string       $published_at
    ): BookDetails
    {
        $book = new Book();
        $book->setTitle($title)
            ->setDescription($description);
        if ($imageFile) {
            $book->setImage($imageFile);
        }

        if ($published_at) {
            $book->setPublishedAt(new DateTime($published_at));
        }

        foreach ($authorIds as $authorId) {
            $author = $this->authorRepository->find($authorId);

            if (!$author) {
                throw new ValidationException(["authors" => "Author with ID {$authorId} doesn't exist."]);
            }

            $book->addAuthor($author);
        }


        $errors = $this->validator->validate($book);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            throw new ValidationException($errorMessages);
        }
        if ($imageFile) {
            $imageName = md5(uniqid()) . '.' . $imageFile->guessExtension();
            try {
                $imageFile->move(
                    $this->uploadsDirectory,
                    $imageName
                );
            } catch (FileException $e) {
                throw $e;
            }
            $book->setImage('/uploads/' . $imageName);
        }

        $this->bookRepository->add($book, true);

        return $this->getBookById($book->getId());
    }

    final public function updateBook(
        int           $id,
        ?string       $title,
        ?string       $description,
        ?array        $authorIds = [],
        ?UploadedFile $imageFile = null,
        ?string       $published_at
    ): BookDetails
    {
        $book = $this->bookRepository->getById($id);
        if ($title) {
            $book->setTitle($title);
        }
        if ($description) {
            $book->setDescription($description);
        }
        if ($imageFile) {
            $book->setImage($imageFile);
        }
        if ($published_at) {
            $book->setPublishedAt(new DateTime($published_at));
        }
        if (!empty($authorIds)) {
            foreach ($authorIds as $authorId) {
                $author = $this->authorRepository->find($authorId);
                if (!$author) {
                    throw new ValidationException(["authors" => "Author with ID {$authorId} doesn't exist."]);
                }
                $book->getAuthors()->clear();

                $book->addAuthor($author);
            }
        }
        $errors = $this->validator->validate($book);

        if (count($errors) > 0) {
            $errorMessages = [];

            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            throw new ValidationException($errorMessages);
        }

        if ($imageFile) {
            $imageName = md5(uniqid()) . '.' . $imageFile->guessExtension();
            try {
                $imageFile->move(
                    $this->uploadsDirectory,
                    $imageName
                );
            } catch (FileException $e) {
                throw $e;
            }
            $book->setImage('/uploads/' . $imageName);
        }

        $this->bookRepository->update($book, true);

        return $this->getBookById($book->getId());
    }

    final public function deleteteBook(
        int           $id
    ): BookDetails
    {
        $book = $this->bookRepository->getById($id);
        $resp = $this->getBookMapping($book);
        $this->bookRepository->remove($book, true);

        return $resp;
    }

    private function getBookMapping(Book $book): BookDetails
    {
        $authors = array_map(fn(Author $author) => new BookAuthorsListItem(
            $author->getId(),
            $author->getFirstName(),
            $author->getLastName(),
            $author->getPatronomicName()
        ), $book->getAuthors()->toArray() ?? []
        );
        return new BookDetails(
            $book->getId(),
            $book->getTitle(),
            $book->getDescription(),
            $book->getImage(),
            $book->getPublishedAt(),
            $authors
        );

    }
}
