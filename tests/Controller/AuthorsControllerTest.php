<?php

namespace App\Tests\Controller;

use App\Controller\AuthorsController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorsControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/authors');
        $responseContent = $client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/responces/AuthorsControllerTest.json',
            $responseContent);
    }
}
