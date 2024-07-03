<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Author;
use App\Entity\Book;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControlerTestCase extends WebTestCase
{
    use JsonAssertions;
    protected KernelBrowser $client;
    protected ?EntityManagerInterface $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->em = self::getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function createBooks(int $i): Book
    {
        $book = (new Book())
            ->setTitle('Test title ' .  $i)
            ->setDescription('Test description ' . $i)
            ->setImage('https://loclhost/image_'. $i . '.png')
            ->setPublishedAt((new DateTime('2024-06-30')));
        return $book;
    }

    protected function createAuthor(int $i): Author
    {
        $author = (new Author())
            ->setFirstName('TestFirstName_' .  $i)
            ->setLastName('TestLastName_' .  $i)
            ->setPatronomicName('TestPatronomicName_' .  $i);
        return $author;
    }
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }


}
