<?php

namespace App\Tests\Controller;

use App\Tests\AbstractControlerTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorsControllerTest extends AbstractControlerTest
{

    public function testIndex()
    {
        for ($i = 0; $i < 5; ++$i) {
            $author = $this->createAuthor($i);
            if ($i == 1 || $i == 2) {
                $book = $this->createBooks($i);
                $author->addBook($book);
                $this->em->persist($book);
            }
            $this->em->persist($author);
            $this->em->flush();
        }
        $this->client->request('GET', '/api/v1/authors');
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
                        'required' => ['id', 'firstName', 'lastName', 'patronomicName'],
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
                            'patronomicName' => ['type' => ['null', 'string']],
                            'books' => [
                                'type' => 'array',
                                'minItems' => 0,
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['id', 'title', 'image'],
                                    'properties' => [
                                        'id' => [
                                            'type' => 'integer'
                                        ],
                                        'title' => [
                                            'type' => 'string'
                                        ],
                                        'image' => [
                                            'type' => 'string'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

}
