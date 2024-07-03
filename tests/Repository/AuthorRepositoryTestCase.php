<?php

namespace App\Tests\Repository;

use App\Entity\Author;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Tests\AbstractRepositoryTestCase;
use DateTime;

class AuthorRepositoryTestCase extends AbstractRepositoryTestCase
{
    private AuthorRepository $authorRepository;
    final protected function setUp(): void
    {
        parent::setUp();
        $this->authorRepository = $this->getRepositoryForEntity(Author::class);
    }

    final public function testFindByPartialName(): void
    {
        $newAuthor = (new Author())->setFirstName('Ann')->setLastName('Test');
        $this->em->persist($newAuthor);

        for ($i=0; $i < 5; ++$i) {
            $book = $this->createBook('book_'. $i, $newAuthor);
            $this->em->persist($book);
        }

        $this->em->flush();
        $authors = $this->authorRepository->findByPartialName('Ann');

        // Loop through the authors and count their books
        $totalBooks = 0;
        foreach ($authors as $author) {
            $totalBooks += count($author->getBooks());
        }

        $this->assertEquals(5, $totalBooks);
    }

    final public function testAuthorNotFound(): void
    {
        $authors = $this->authorRepository->findByPartialName('NonExistingName');

        $this->assertFalse(count($authors) > 0, "No authors should be found");
    }

    private function createBook(string $string, Author $author): Book
    {
        $book = (new Book())->setTitle($string)
            ->setDescription('Test description for '. $string)
            ->setImage('test img')
            ->setPublishedAt((new DateTime('2024-06-30')))
            ->addAuthor($author);

        $author->addBook($book);

        return $book;
    }
}
