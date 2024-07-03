<?php

namespace App\Tests\Controller;

use App\Entity\Author;
use App\Entity\Book;
use App\Tests\AbstractControlerTestCase;
use DateTime;

class BooksControllerTestCase extends AbstractControlerTestCase
{
    public function testIndex()
    {
        for ($i = 0; $i < 5; ++$i) {
            $book = $this->createBooks($i);
            $author = $this->createAuthor($i);
            $book->addAuthor($author);
            $this->em->persist($author);
            $this->em->persist($book);
            $this->em->flush();
        }
        $this->client->request('GET', '/api/v1/books');
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['currentPage', 'totalPages', 'itemsPerPage', 'items'],
            'properties' => [
                'currentPage' => [
                    'type' => 'integer'
                ],
                'totalPages' => [
                    'type' => 'integer'
                ],
                'itemsPerPage' => [
                    'type' => 'integer'
                ],
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'required' => ['id', 'title', 'description', 'image', 'publishedAt', 'authors'],
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'integer'
                        ],
                        'title' => [
                            'type' => 'string'
                        ],
                        'description' => [
                            'type' => 'string'
                        ],
                        'image' => [
                            'type' => 'string'
                        ],
                        'publishedAt' => [
                            'type' => 'string'
                        ],
                        'authors' => [
                            'type' => 'array',
                            'minItems' => 0,
                            'items' => [
                                'type' => 'object',
                                'required' => ['id', 'firstName', 'lastName', 'patronomicName'],
                            ],
                            'properties' => [
                                'id' => [
                                    'type' => 'integer'
                                ],
                                'firstName' => [
                                    'type' => 'string'
                                ],
                                'lastName' => [
                                    'type' => 'string'
                                ],
                                'patronomicName' => ['type' => ['null', 'string']]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
    public function testShowBookDetails(): void
    {

        $book = $this->createBooks(1);
        $author = $this->createAuthor(1);
        $book->addAuthor($author);
        $this->em->persist($author);
        $this->em->persist($book);
        $this->em->flush();

        $this->client->request('GET', '/api/v1/books/' . $book->getId());
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertResponseIsSuccessful();
        $this->assertJsonDocumentMatchesSchema($responseContent, [
            'type' => 'object',
            'required' => ['id', 'title', 'description', 'image', 'publishedAt', 'authors'],
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'title' => [
                    'type' => 'string'
                ],
                'description' => [
                    'type' => 'string'
                ],
                'image' => [
                    'type' => 'string'
                ],
                'publishedAt' => [
                    'type' => 'string'
                ],
                'authors' => [
                    'type' => 'array',
                    'minItems' => 0,
                    'items' => [
                        'type' => 'object',
                        'required' => ['id', 'firstName', 'lastName', 'patronomicName'],
                    ],
                    'properties' => [
                        'id' => [
                            'type' => 'integer'
                        ],
                        'firstName' => [
                            'type' => 'string'
                        ],
                        'lastName' => [
                            'type' => 'string'
                        ],
                        'patronomicName' => ['type' => ['null', 'string']]
                    ]
                ]
            ]
        ]);
    }
    public function testShowBookDetailsNotFound(): void
    {
        $nonExistingId = 123;
        $this->client->request('GET', '/api/v1/books/' . $nonExistingId);

        $responseContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('message', $responseContent);
        $this->assertEquals('Book not found', $responseContent['message']);
    }
}
