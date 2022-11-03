<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Call\Result;

use SR\Silencer\Call\Runner\ClosureRunner;

final class ResultInspector
{
    /**
     * @var \Closure
     */
    private ?\Closure $validatorClosure = null;

    /**
     * @var object
     */
    private ?object $validatorBinding = null;

    private mixed $return;

    /**
     * @var string[]|null
     */
    private ?array $raised;

    private bool $called;

    public function __construct(\Closure $validatorClosure = null, object $validatorBinding = null)
    {
        $this->validatorClosure = $validatorClosure;
        $this->validatorBinding = $validatorBinding ?: $this->validatorBinding;
        $this->called = false;
    }

    public function setReturn(mixed $result, array $raised = null): self
    {
        $this->return = $result;
        $this->raised = $raised;
        $this->called = true;

        return $this;
    }

    public function isCalled(): bool
    {
        return true === $this->called;
    }

    public function hasReturn(): bool
    {
        return null !== $this->return;
    }

    public function getReturn(): mixed
    {
        return $this->return;
    }

    public function isEqual(mixed $comparison): bool
    {
        return $this->return === $comparison;
    }

    public function isTrue(): bool
    {
        return $this->isEqual(true);
    }

    public function isFalse(): bool
    {
        return $this->isEqual(false);
    }

    public function isValid(): bool
    {
        if (!$this->validatorClosure) {
            return true !== $this->hasError();
        }

        try {
            [$return] = (new ClosureRunner($this->validatorClosure, $this->validatorBinding))->run(
                $this->return,
                $this->raised,
                $this
            );

            return (bool) $return;
        } catch (\RuntimeException $exception) {
            return false;
        }
    }

    /**
     * Return true if an error was raised by invoking closure.
     */
    public function hasError(): bool
    {
        return null !== $this->raised;
    }

    public function getError(string $index = null): mixed
    {
        if (null === $this->raised) {
            return null;
        }

        return null === $index ? $this->raised : $this->raised[$index];
    }

    public function getErrorMessage(): ?string
    {
        return $this->getError('message');
    }

    public function getErrorType(): ?int
    {
        return $this->getError('type');
    }
}
