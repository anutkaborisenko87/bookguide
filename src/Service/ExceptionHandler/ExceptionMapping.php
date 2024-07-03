<?php

namespace App\Service\ExceptionHandler;

class ExceptionMapping
{
    private int $code;
    private bool $hidden;
    private bool $loggable;
    public function __construct(int $code, bool $hidden, bool $loggable)
    {
        $this->code = $code;
        $this->hidden = $hidden;
        $this->loggable = $loggable;
    }

    public static function fromCode(int $code): self
    {
        return new self($code, true, false);
    }

    final public function getCode(): int
    {
        return $this->code;
    }

    final public function isHidden(): bool
    {
        return $this->hidden;
    }

    final public function isLoggable(): bool
    {
        return $this->loggable;
    }

}
