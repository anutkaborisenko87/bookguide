<?php

namespace App\Tests\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Exception\ValidationException;
use App\Model\AuthorListItem;
use App\Model\AuthorsBooksListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;
use App\Service\AutthorsService;
use App\Tests\AbstractTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AutthorsServiceTest extends AbstractTestCase
{
    private ValidatorInterface $validator;
    private AuthorRepository $authorRepository;

    final protected function setUp(): void
    {
        parent::setUp();
        $this->authorRepository = $this->createMock(AuthorRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }
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

        $this->authorRepository->expects($this->once())
            ->method('findBy')
            ->with([], null, 10, 0)
            ->willReturn([$author]);
        $this->authorRepository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(1);
        $service = $this->createService($this->authorRepository);
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

    public function testCreateAuthorWorksCorrectly(): void
    {
        $firstName = 'TestFirstName';
        $lastName = 'TestLastName';
        $patronomicName = 'TestPatronomicName';

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->authorRepository->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(Author::class), true)
            ->will($this->returnCallback(function ($author) {
                $this->setEntityId($author, 1);
            }));

        $service = $this->createService($this->authorRepository);

        $result = $service->createAuthor($firstName, $lastName, $patronomicName);

        $this->assertInstanceOf(AuthorListItem::class, $result);
        $this->assertEquals($firstName, $result->getFirstName());
        $this->assertEquals($lastName, $result->getLastName());
        $this->assertEquals($patronomicName, $result->getPatronomicName());
    }

    public function testValiidateAuthorDataWhenCreate(): void
    {
        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation(
                    'First Name is required.',
                    null,
                    [],
                    '',
                    'first_name',
                    null
                ),
                new ConstraintViolation(
                    'Last Name is required.',
                    null,
                    [],
                    '',
                    'last_name',
                    null
                )
            ]));

        $service = $this->createService($this->authorRepository);
        $this->expectException(ValidationException::class);
        $result = $service->createAuthor('', '');

    }

    private function createService(AuthorRepository $repository): AutthorsService
    {
        return new AutthorsService($repository, $this->validator);

    }
}
