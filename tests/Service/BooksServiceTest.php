<?php

namespace App\Tests\Service;

use App\Entity\Book;
use App\Model\BookListItem;
use App\Model\BookListResponse;
use App\Repository\BookRepository;
use App\Service\BooksService;
use App\Tests\AbstractTestCase;
use DateTime;

class BooksServiceTest extends AbstractTestCase
{

    final public function testGetAllBooks(): void
    {
        $book = (new Book())
            ->setTitle('TestTitle')
            ->setDescription('Test description')
            ->setImage('http:://localhost/testimage.png')
            ->setPublishedAt(new DateTime('2024-06-23'));
        $this->setEntityId($book, 1);

        $repository = $this->createMock(BookRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with([], null, 10, 0)
            ->willReturn([$book]);
        $repository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(1);

        $service = new BooksService($repository);
        $date = new DateTime('2024-06-23');
        $expected = new BookListResponse([new BookListItem(
            1,
            'TestTitle',
            'Test description',
            'http:://localhost/testimage.png',
            $date
        )

        ], 1, 1, 10);
        $this->assertEquals($expected, $service->getAllBooks(1));
    }
}
