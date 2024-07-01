<?php

namespace App\DataFixtures;

use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class
AuthorFixtures extends Fixture
{
    final public function load(ObjectManager $manager): void
    {
        foreach($this->getAuthorsData() as [$fistname, $lastName, $patronomicName] )
        {
            $author = new Author();
            $author->setFirstName($fistname);
            $author->setLastName($lastName);
            $author->setPatronomicName($patronomicName);
            $manager->persist($author);
        }
        $manager->flush();
    }

    private function getAuthorsData(): array
    {
        return [
            ['John', 'Doe', null],
            ['Taras', 'Shevchenko', 'Grigorievich'],
            ['Olga', 'Kobylianska', null],
        ];
    }
}
