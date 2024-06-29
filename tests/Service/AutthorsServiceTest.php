<?php

namespace App\Tests\Service;

use App\Entity\Author;
use App\Model\AuthorListItem;
use App\Model\AuthorsListResponse;
use App\Repository\AuthorRepository;
use App\Service\AutthorsService;
use PHPUnit\Framework\TestCase;

class AutthorsServiceTest extends TestCase
{

    public function testGetAuthors()
    {
        $repository = $this->createMock(AuthorRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with([], null, 10, 0)
            ->willReturn([(new Author())
                ->setId(1)
                ->setFirstName('TestFirstName')
                ->setLastName('TestLastName')
                ->setPatronomicName('TestPatronomicName')
            ]);
        $repository->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(1);
        $service = new AutthorsService($repository);
        $expected = new AuthorsListResponse([new AuthorListItem(
            1,
            'TestFirstName',
            'TestLastName',
            'TestPatronomicName')
        ], 1, 1, 10);
        $this->assertEquals($expected, $service->getAuthors(1));
    }
}
