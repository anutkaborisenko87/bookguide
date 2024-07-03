<?php

namespace App\Tests\Service\ExceptionHandler;

use App\Service\ExceptionHandler\ExceptionMappingResolver;
use App\Tests\AbstractTestCase;
use InvalidArgumentException;
use LogicException;

class ExceptionMappingResolverTest extends AbstractTestCase
{

    final public function testResolveThrowsExceptionOnEmptyCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ExceptionMappingResolver(['someClass' => ['hidden' => true]]);
    }

    final public function testResolveReturnsNullWhenNotFound(): void
    {
        $resolver = new ExceptionMappingResolver([]);

        $this->assertNull($resolver->resolve(InvalidArgumentException::class));
    }

    final public function testResolvesClassItSelf(): void
    {
        $resolver = new ExceptionMappingResolver([InvalidArgumentException::class => ['code' => 400]]);
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertEquals(400, $mapping->getCode());
        $this->assertFalse($mapping->isLoggable());
        $this->assertTrue($mapping->isHidden());
    }

    final public function testResolvesSubClass(): void
    {
        $resolver = new ExceptionMappingResolver([LogicException::class => ['code' => 500]]);
        $mapping = $resolver->resolve(InvalidArgumentException::class);
        $this->assertEquals(500, $mapping->getCode());
        $this->assertFalse($mapping->isLoggable());
        $this->assertTrue($mapping->isHidden());
    }

    final public function testResolvesHiden(): void
    {
        $resolver = new ExceptionMappingResolver([LogicException::class => ['code' => 500, 'hidden' => false]]);
        $mapping = $resolver->resolve(LogicException::class);
        $this->assertFalse($mapping->isHidden());
    }

    final public function testResolvesLoggable(): void
    {
        $resolver = new ExceptionMappingResolver([LogicException::class => ['code' => 500, 'loggable' => true]]);
        $mapping = $resolver->resolve(LogicException::class);
        $this->assertTrue($mapping->isLoggable());
    }
}
