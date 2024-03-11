<?php

declare(strict_types=1);

namespace HSkrasek\LaravelZeroOAuth;

final readonly class Error implements \Throwable
{
    private function __construct(
        private string $message,
        private int $code,
        private ?\Throwable $previous = null
    ) {
    }

    public static function fromMessage(string $message): self
    {
        return new self(
            message: $message,
            code: 0
        );
    }

    public static function fromThrowable(\Throwable $throwable, ?string $message = null): self
    {
        return new self(
            message: $message ?? $throwable->getMessage(),
            code: $throwable->getCode(),
            previous: $throwable->getPrevious(),
        );
    }

    #[\Override]
    public function getMessage(): string
    {
        return $this->message;
    }

    #[\Override]
    public function getCode(): int
    {
        return $this->code;
    }

    #[\Override]
    public function getFile(): string
    {
        return $this->getPrevious()?->getFile() ?? '';
    }

    #[\Override]
    public function getLine(): int
    {
        return $this->getPrevious()?->getLine() ?? 0;
    }

    #[\Override]
    public function getTrace(): array
    {
        return $this->getPrevious()?->getTrace() ?? [];
    }

    #[\Override]
    public function getTraceAsString(): string
    {
        return $this->getPrevious()?->getTraceAsString() ?? '';
    }

    #[\Override]
    public function getPrevious(): ?\Throwable
    {
        return $this->previous;
    }

    public function __toString()
    {
        return \sprintf(
            '%s: %s in %s:%s',
            Error::class,
            $this->message,
            $this->getFile(),
            $this->getLine()
        );
    }
}
