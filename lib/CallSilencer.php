<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer;

use SR\Silencer\Util\EngineError;

/**
 * Simple API for calling closure in error-silenced context.
 */
final class CallSilencer implements CallSilencerInterface
{
    /**
     * Closure instance invoked in silenced environment.
     *
     * @var \Closure
     */
    private $invokable;

    /**
     * Alternate object context to bind closure to.
     *
     * @var object
     */
    private $invokableBind;

    /**
     * Closure instance used to determine validity of main closure call.
     *
     * @var \Closure
     */
    private $validator;

    /**
     * Alternate object context to bind validation closure to.
     *
     * @var object
     */
    private $validatorBind;

    /**
     * Returning value of invoked closure.
     *
     * @var mixed
     */
    private $result;

    /**
     * Raised error if an error was raised during invoking closure.
     *
     * @var string[]|null
     */
    private $raisedError;

    /**
     * False if error reporting level should NOT be restored after invoking silenced closure.
     *
     * @var bool
     */
    private $restore;

    /**
     * True if closure has been invoked.
     *
     * @var bool
     */
    private $invoked;

    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokable Closure instance called in silenced environment
     * @param \Closure|null $validator Optional closure that determines validity of return value
     * @param object        $bind      Optional binding context to apply to closures
     */
    public function __construct(\Closure $invokable = null, \Closure $validator = null, $bind = null)
    {
        $this
            ->setInvokable($invokable, $bind)
            ->setValidator($validator, $bind);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokable Closure instance called in silenced environment
     * @param \Closure|null $validator Optional closure that determines validity of return value
     * @param object        $bind      Optional binding context to apply to closures
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $invokable = null, \Closure $validator = null, $bind = null) : CallSilencerInterface
    {
        return new static($invokable, $validator, $bind);
    }

    /**
     * Disables restoring error reporting level after invoking silenced closure.
     *
     * @return CallSilencerInterface
     */
    public function disableSilencerRestoration() : CallSilencerInterface
    {
        $this->restore = false;

        return $this;
    }

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $invokable A closure to call in silenced environment
     * @param object   $bind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setInvokable(\Closure $invokable = null, $bind = null) : CallSilencerInterface
    {
        list($this->invokable, $this->invokableBind) = [$invokable, $bind];

        return $this;
    }

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validator An instance of \Closure called to determine validity of return value and/or raised error
     * @param object   $bind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setValidator(\Closure $validator = null, $bind = null) : CallSilencerInterface
    {
        list($this->validator, $this->validatorBind) = [$validator, $bind];

        return $this;
    }

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     *
     * @throws \Exception If an exception is thrown within the \Closure instance
     *
     * @return CallSilencerInterface
     */
    public function invoke(...$parameters) : CallSilencerInterface
    {
        if (!$this->invokable) {
            return $this;
        }

        $result = null;

        try {
            $this->invokeSetUp();
            $result = $this->invokeClosure($this->invokable, $this->invokableBind, ...$parameters);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->invokeTearDown($result);
        }

        return $this;
    }

    /**
     * Returns true if the closure was called.
     *
     * @return bool
     */
    public function isInvoked() : bool
    {
        return $this->invoked === true;
    }

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult() : bool
    {
        return $this->result !== null;
    }

    /**
     * Get the return value invoked closure.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Returns equality of passed value to result.
     *
     * @param mixed $what
     *
     * @return bool
     */
    public function isResult($what) : bool
    {
        return $this->result === $what;
    }

    /**
     * Returns true if the invoked closure return value is true (strict check).
     *
     * @return bool
     */
    public function isResultTrue() : bool
    {
        return $this->isResult(true);
    }

    /**
     * Returns true if the invoked closure return value is false (strict check).
     *
     * @return bool
     */
    public function isResultFalse() : bool
    {
        return $this->isResult(false);
    }

    /**
     * Returns type hinted value from validation closure.
     *
     * @return bool
     */
    public function isResultValid() : bool
    {
        if (!$this->validator) {
            return !$this->hasError();
        }

        return $this->invokeClosure($this->validator, $this->validatorBind, $this->result, $this->raisedError) ? true : false;
    }

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError() : bool
    {
        return null !== $this->raisedError;
    }

    /**
     * @param string|null $index
     *
     * @return string|int|mixed[]
     */
    public function getError(string $index = null)
    {
        if (null === $this->raisedError) {
            return null;
        }

        return $index === null ? $this->raisedError : $this->raisedError[$index];
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

    /**
     * @param \Closure    $closure
     * @param object|null $bind
     * @param mixed       ...$parameters
     *
     * @return mixed
     */
    private function invokeClosure(\Closure $closure, $bind = null, ...$parameters)
    {
        if (null !== $bind) {
            $closure = $closure->bindTo($bind, $bind);
        }

        return $closure(...$parameters);
    }

    /**
     * @return CallSilencerInterface
     */
    private function invokeSetUp() : CallSilencerInterface
    {
        $this->invokeState(false, null, null);

        Silencer::silenceIfNot();
        EngineError::clearLastError();

        return $this;
    }

    /**
     * @param mixed $result
     *
     * @return CallSilencerInterface
     */
    private function invokeTearDown($result) : CallSilencerInterface
    {
        $this->invokeState(true, EngineError::getLastError(), $result);

        if (false !== $this->restore) {
            Silencer::restore();
        }

        return $this;
    }

    /**
     * @param bool         $invoked
     * @param mixed[]|null $raisedError
     * @param mixed        $result
     */
    private function invokeState(bool $invoked, array $raisedError = null, $result)
    {
        list($this->invoked, $this->raisedError, $this->result) =
            [$invoked, $raisedError, $result];
    }
}

/* EOF */
