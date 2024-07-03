<?php

namespace App\Tests\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Exception\BookNotFoundException;
use App\Model\BookDetails;
use App\Model\BookListResponse;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Service\BooksService;
use App\Tests\AbstractTestCase;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BooksServiceTest extends AbstractTestCase
{
    private BookRepository $bookRepo;
    private ValidatorInterface $validator;
    private AuthorRepository $authorRepo;
    private string $uploadsDirectory;
    private Filesystem $filesystem;

    final protected function setUp(): void
    {
        parent::setUp();
        $this->bookRepo = $this->createMock(BookRepository::class);
        $this->authorRepo = $this->createMock(AuthorRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->uploadsDirectory = sys_get_temp_dir() . '/' . uniqid('uploads_', true);
        mkdir($this->uploadsDirectory, 0700, true);
        $this->filesystem = $this->createMock(Filesystem::class);
    }

    final public function testGetAllBooks(): void
    {
        $book = (new Book())
            ->setTitle('TestTitle')
            ->setDescription('Test description')
            ->setImage('http:://localhost/testimage.png')
            ->setPublishedAt(new DateTime('2024-06-23'));
        $this->setEntityId($book, 1);

        $this->bookRepo->expects($this->once())
            ->method('findBy')
            ->with([], null, 10, 0)
            ->willReturn([$book]);
        $this->bookRepo->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(1);

        $service = $this->createService($this->bookRepo, $this->validator, $this->authorRepo);
        $date = new DateTime('2024-06-23');
        $expected = new BookListResponse([new BookDetails(
            1,
            'TestTitle',
            'Test description',
            'http:://localhost/testimage.png',
            $date
        )

        ], 1, 1, 10);
        $this->assertEquals($expected, $service->getAllBooks(1));
    }

    final public function testNotFoundBookById(): void
    {
        $this->bookRepo->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willThrowException(new BookNotFoundException());

        $service = $this->createService($this->bookRepo, $this->validator, $this->authorRepo);

        $this->expectException(BookNotFoundException::class);
        $service->getBookById(1);

    }

    final public function testFoundBookById(): void
    {
        $book = (new Book())
            ->setTitle('TestTitle')
            ->setDescription('Test description')
            ->setImage('http:://localhost/testimage.png')
            ->setPublishedAt(new DateTime('2024-06-23'));
        $this->setEntityId($book, 1);

        $this->bookRepo->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($book);

        $service = $this->createService($this->bookRepo, $this->validator, $this->authorRepo);
        $date = new DateTime('2024-06-23');
        $expected = new BookDetails(
            1,
            'TestTitle',
            'Test description',
            'http:://localhost/testimage.png',
            $date
        );
        $this->assertEquals($expected, $service->getBookById(1));

    }

    final public function testCreateBook(): void
    {
        $originalPath = __DIR__ . '/../Resources/testImage.jpg';
        $copyPath = sys_get_temp_dir() . '/' . uniqid('testImage', true) . '.jpg';
        copy($originalPath, $copyPath);
        $uploadedFile = new UploadedFile(
            $copyPath,
            'testImage.jpg',
            'image/jpg',
            null,
            true
        );

        $this->authorRepo->expects($this->once())
            ->method('find')
            ->willReturn(new Author());
        $title = 'testTitle';
        $description = 'testDescription';
        $authorIds = [1];
        $publishedAt = '2000-01-01';

        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->bookRepo->expects($this->once())
            ->method('add')
            ->will($this->returnCallback(function ($book) {
                $this->setEntityId($book, 1);
            }));
        $service = $this->createService($this->bookRepo, $this->validator, $this->authorRepo);
        $result = $service->createBook($title, $description, $authorIds, $uploadedFile, $publishedAt);
        $this->assertInstanceOf(BookDetails::class, $result);

    }
    public function testCreateBookWithValidationErrors(): void
    {
        $originalPath = __DIR__ . '/../Resources/testImage.jpg';
        $copyPath = sys_get_temp_dir() . '/' . uniqid('testImage', true) . '.jpg';
        copy($originalPath, $copyPath);

        $uploadedFile = new UploadedFile(
            $copyPath,
            'testImage.jpg',
            'image/jpg',
            null,
            true
        );
        $this->validator->expects($this->once())
            ->method('validate')
            ->willReturn(new \Symfony\Component\Validator\ConstraintViolationList([
                new \Symfony\Component\Validator\ConstraintViolation(
                    'Title is required.',
                    null,
                    [],
                    '',
                    'title',
                    null
                ),
                new \Symfony\Component\Validator\ConstraintViolation(
                    'Description is required.',
                    null,
                    [],
                    '',
                    'description',
                    null
                ),
                new \Symfony\Component\Validator\ConstraintViolation(
                    'At least one author is required.',
                    null,
                    [],
                    '',
                    'authorIds',
                    null
                ),
                new \Symfony\Component\Validator\ConstraintViolation(
                    'PublishedAt is required.',
                    null,
                    [],
                    '',
                    'publishedAt',
                    null
                )
            ]));

        $service = $this->createService($this->bookRepo, $this->validator, $this->authorRepo);
        $this->expectException(\App\Exception\ValidationException::class);
        $result = $service->createBook('', '', [],$uploadedFile, '');

    }

    private function createService(BookRepository   $bookRepository, ValidatorInterface $validator,
                                   AuthorRepository $authorRepository): BooksService
    {
        return new BooksService($bookRepository, $validator, $authorRepository, $this->filesystem, $this->uploadsDirectory);
    }
}
