<?php

namespace App\Tests\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Model\AuthorListItem;
use App\Model\AuthorsBooksListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;
use App\Service\AutthorsService;
use App\Tests\AbstractTestCase;

class AutthorsServiceTest extends AbstractTestCase
{

    final public function testGetAuthors(): void
    {
        $book = $this->createMock(Book::class);
        $book->method('getId')->willReturn(1);
        $book->method('getTitle')->willReturn('TestBookTitle');
        $book->method('getImage')->willReturn('TestBookImage');
        $author = (new Author())
            ->setFirstName('TestFirstName')
            ->setLastName('TestLastName')
            ->setPatronomicName('TestPatronomicName')
            ->addBook($book);
        $this->setEntityId($author, 1);
        $repository = $this->createMock(AuthorRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with([], null, 10, 0)
            ->willReturn([$author]);
        $repository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(1);
        $service = new AutthorsService($repository);
        $expectedBookListItem = new AuthorsBooksListItem(
            1,
            'TestBookTitle',
            'TestBookImage'
        );
        $expected = new AuthorsListResponse([new AuthorListItem(
            1,
            'TestFirstName',
            'TestLastName',
            'TestPatronomicName',
            [$expectedBookListItem]
        )
        ], 1, 1, 10);
        $this->assertEquals($expected, $service->getAuthors(1));
    }
}
