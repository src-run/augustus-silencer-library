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
    private $validatorClosure;

    /**
     * @var object
     */
    private $validatorBinding;

    /**
     * @var mixed
     */
    private $return;

    /**
     * @var string[]|null
     */
    private $raised;

    /**
     * @var bool
     */
    private $called;

    /**
     * @param \Closure|null $validatorClosure
     * @param object|null   $validatorBinding
     */
    public function __construct(\Closure $validatorClosure = null, $validatorBinding = null)
    {
        $this->validatorClosure = $validatorClosure;
        $this->validatorBinding = $validatorBinding ?: $this->validatorBinding;
        $this->called = false;
    }

    /**
     * @param mixed      $result
     * @param array|null $raised
     *
     * @return self
     */
    public function setReturn($result, array $raised = null): self
    {
        $this->return = $result;
        $this->raised = $raised;
        $this->called = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCalled(): bool
    {
        return true === $this->called;
    }

    /**
     * @return bool
     */
    public function hasReturn(): bool
    {
        return null !== $this->return;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @param mixed $comparison
     *
     * @return bool
     */
    public function isEqual($comparison): bool
    {
        return $this->return === $comparison;
    }

    /**
     * @return bool
     */
    public function isTrue(): bool
    {
        return $this->isEqual(true);
    }

    /**
     * @return bool
     */
    public function isFalse(): bool
    {
        return $this->isEqual(false);
    }

    /**
     * @return bool
     */
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
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return null !== $this->raised;
    }

    /**
     * @param string|null $index
     *
     * @return string|int|array|null
     */
    public function getError(string $index = null)
    {
        if (null === $this->raised) {
            return null;
        }

        return null === $index ? $this->raised : $this->raised[$index];
    }

    /**
     * @return string
     */
    public function getErrorMessage(): ?string
    {
        return $this->getError('message');
    }

    /**
     * @return int
     */
    public function getErrorType(): ?int
    {
        return $this->getError('type');
    }
}
