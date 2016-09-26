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
     * @var mixed
     */
    private $result;

    /**
     * @var string[]|null
     */
    private $raised;

    /**
     * @var \Closure
     */
    private $validatorInst;

    /**
     * @var object
     */
    private $validatorBind;

    /**
     * True if closure has been invoked.
     *
     * @var bool
     */
    private $called;

    /**
     * @param mixed         $result
     * @param mixed[]|null  $raised
     * @param bool          $called
     * @param \Closure|null $validatorInst
     * @param object        $validatorBind
     */
    public function __construct($result, array $raised = null, $called = true, \Closure $validatorInst = null, $validatorBind = null)
    {
        $this->result = $result;
        $this->raised = $raised;
        $this->called = $called;

        $this->setValidator($validatorInst, $validatorBind);
    }

    /**
     * @param mixed         $result
     * @param mixed[]|null  $raised
     * @param bool          $called
     * @param \Closure|null $validatorInst
     * @param object        $validatorBind
     *
     * @return ResultInfoInterface
     */
    public static function create($result, array $raised = null, $called = true, \Closure $validatorInst = null, $validatorBind = null) : ResultInfoInterface
    {
        return new static($result, $raised, $called, $validatorInst, $validatorBind);
    }

    /**
     * @param \Closure $validatorInst
     * @param object   $validatorBind
     *
     * @return ResultInfoInterface
     */
    public function setValidator(\Closure $validatorInst = null, $validatorBind = null) : ResultInfoInterface
    {
        $this->validatorInst = $validatorInst;
        $this->validatorBind = $validatorBind ?: $this->validatorBind;

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
    public function get()
    {
        return $this->getResult();
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function has() : bool
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
        if (!$this->validatorInst) {
            return !$this->hasError();
        }

        $runner = ClosureRunner::create($this->validatorInst, $this->validatorBind);
        $runner->invoke($valid, $ignore, $this->result, $this->raised, $this);

        return (bool) $valid;
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
