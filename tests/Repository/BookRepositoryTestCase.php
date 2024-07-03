<?php

namespace App\Tests\Repository;

use App\Entity\Author;
use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Repository\BookRepository;
use App\Tests\AbstractRepositoryTestCase;
use DateTime;

class BookRepositoryTestCase extends AbstractRepositoryTestCase
{
    private BookRepository $bookRepository;

    final protected function setUp(): void
    {
        parent::setUp();
        $this->bookRepository = $this->getRepositoryForEntity(Book::class);

    }

    final public function testNotFoundById(): void
    {
        $this->expectException(BookNotFoundException::class);
        $book = $this->bookRepository->getById(123);
    }

    final public function testFoundById(): void
    {
        $newAuthor = (new Author())->setFirstName('Ann')->setLastName('Test');
        $newBook = (new Book())->setTitle('Test title')
            ->setDescription('Test description for book')
            ->setImage('test img')
            ->setPublishedAt((new DateTime('2024-06-30')))
            ->addAuthor($newAuthor);
        $this->em->persist($newAuthor);
        $this->em->persist($newBook);
        $this->em->flush();
        $bookId = $newBook->getId();
        $loadedBook = $this->bookRepository->getById($bookId);
        $this->assertInstanceOf(Book::class, $loadedBook);
        $this->assertEquals($newBook->getTitle(), $loadedBook->getTitle());
    }

}
