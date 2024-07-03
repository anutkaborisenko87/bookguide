<?php

declare(strict_types=1);

namespace App\Tests;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractRepositoryTestCase extends KernelTestCase
{
    protected ?EntityManager $em;

    protected function setUp(): void
    {
        parent::setUp();
        $this->em = self::getContainer()->get('doctrine.orm.entity_manager');
    }

    protected function getRepositoryForEntity(string $entityClass)
    {
        return $this->em->getRepository($entityClass);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}
