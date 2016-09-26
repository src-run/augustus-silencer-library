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

final class ResultInfo implements ResultInfoInterface
{
    /**
     * @var \Closure
     */
    private $validator;

    /**
     * @var object
     */
    private $binding;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var string[]|null
     */
    private $raised;

    /**
     * @var bool
     */
    private $called = false;

    /**
     * @param \Closure|null $validator
     * @param object        $binding
     */
    public function __construct(\Closure $validator = null, $binding = null)
    {
        $this->setValidator($validator, $binding);
    }

    /**
     * @param \Closure $validator
     * @param object   $binding
     *
     * @return ResultInfoInterface
     */
    public function setValidator(\Closure $validator = null, $binding = null) : ResultInfoInterface
    {
        $this->validator = $validator;
        $this->binding = $binding ?: $this->binding;

        return $this;
    }

    /**
     * @param mixed      $result
     * @param array|null $raised
     * @param bool       $called
     *
     * @return ResultInfoInterface
     */
    public function setResult($result, array $raised = null, $called = true) : ResultInfoInterface
    {
        $this->result = $result;
        $this->raised = $raised;
        $this->called = $called;

        return $this;
    }

    /**
     * Returns true if the closure was called.
     *
     * @return bool
     */
    public function isCalled() : bool
    {
        return $this->called === true;
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function hasReturn() : bool
    {
        return $this->result !== null;
    }

    /**
     * @param mixed $comparison
     *
     * @return bool
     */
    public function isEquitable($comparison) : bool
    {
        return $this->result === $comparison;
    }

    /**
     * @return bool
     */
    public function isTrue() : bool
    {
        return $this->isEquitable(true);
    }

    /**
     * @return bool
     */
    public function isFalse() : bool
    {
        return $this->isEquitable(false);
    }

    /**
     * @return bool
     */
    public function isValid() : bool
    {
        if (!$this->validator) {
            return !$this->hasError();
        }

        list($result) = ClosureRunner::create()
            ->setInvokable($this->validator, $this->binding)
            ->runInvokable($this->result, $this->raised, $this);

        return (bool) $result;
    }

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError() : bool
    {
        return null !== $this->raised;
    }

    /**
     * @param string|null $index
     *
     * @return string|int|mixed[]
     */
    public function getError(string $index = null)
    {
        if (null === $this->raised) {
            return null;
        }

        return $index === null ? $this->raised : $this->raised[$index];
    }

    /**
     * Return the error message caused by a call in the invoked closure.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->getError('message');
    }

    /**
     * Return the error type integer caused by a call in the invoked closure.
     *
     * @return int
     */
    public function getErrorType()
    {
        return $this->getError('type');
    }
}

/* EOF */
