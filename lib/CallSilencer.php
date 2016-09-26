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
    private $invokableInst;

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
    private $validatorInst;

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
    private $restoreReportingLevel;

    /**
     * True if closure has been invoked.
     *
     * @var bool
     */
    private $called;

    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param object        $invokableBind Binding for main invokable closure call
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     * @param object        $validatorBind Binding for result validation closure
     */
    public function __construct(\Closure $invokableInst = null, $invokableBind = null, \Closure $validatorInst = null, $validatorBind = null)
    {
        $this->setInvokable($invokableInst, $invokableBind);
        $this->setValidator($validatorInst, $validatorBind);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $invokableInst = null, \Closure $validatorInst = null) : CallSilencerInterface
    {
        return new static($invokableInst, null, $validatorInst, null);
    }

    /**
     * Disables restoring error reporting level after invoking silenced closure.
     *
     * @return CallSilencerInterface
     */
    public function disableSilencerRestoration() : CallSilencerInterface
    {
        $this->restoreReportingLevel = false;

        return $this;
    }

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $invokableInst A closure to call in silenced environment
     * @param object   $invokableBind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setInvokable(\Closure $invokableInst = null, $invokableBind = null) : CallSilencerInterface
    {
        $this->invokableInst = $invokableInst;
        $this->setInvokableBind($invokableBind);

        return $this;
    }

    /**
     * Assign an alternate binding context/scope for main invokable closure. By default it is not re-bound
     * and will have the context/scope of the object it was originally defined in.
     *
     * @param null|object $invokableBind Bind to apply to main invokable closure
     *
     * @return CallSilencerInterface
     */
    public function setInvokableBind($invokableBind = null) : CallSilencerInterface
    {
        $this->invokableBind = $invokableBind ?: $this->invokableBind;

        return $this;
    }

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validatorInst An instance of \Closure called to determine validity of return value and/or raised error
     * @param object   $validatorBind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setValidator(\Closure $validatorInst = null, $validatorBind = null) : CallSilencerInterface
    {
        $this->validatorInst = $validatorInst;
        $this->setValidatorBind($validatorBind);

        return $this;
    }

    /**
     * Assign an alternate binding context/scope for validation closure. By default it is bound
     * to the silencer instance itself, allowing it to access the array of helper methods regarding
     * return values and errors.
     *
     * @param null|object $validatorBind Bind to apply to result validation closure
     *
     * @return CallSilencerInterface
     */
    public function setValidatorBind($validatorBind = null) : CallSilencerInterface
    {
        $this->validatorBind = $validatorBind ?: $this->validatorBind;

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
        if (false === $this->invokableInst instanceof \Closure) {
            return $this;
        }

        $results = null;

        try {
            $this->preInvokeActions();
            $results = $this->doInvoke($this->invokableInst, $this->invokableBind, ...$parameters);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->postInvokeActions($results);
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
        return $this->called === true;
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
        if (!$this->validatorInst) {
            return !$this->hasError();
        }

        return (bool) $this->doInvoke(
            $this->validatorInst, $this->validatorBind, $this->result, $this->raisedError, $this);
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
     * @param \Closure $closure
     * @param mixed    $binding
     * @param mixed    ...$parameters
     *
     * @return mixed
     */
    private function doInvoke(\Closure $closure, $binding = null, ...$parameters)
    {
        if ($binding) {
            $closure = $closure->bindTo($binding, $binding);
        }

        return $closure(...$parameters);
    }

    /**
     * @return void
     */
    private function preInvokeActions()
    {
        $this->assignSelfState(false, null, null);

        Silencer::silenceIfNot();
        EngineError::clearLastError();
    }

    /**
     * @param mixed $result
     *
     * @return void
     */
    private function postInvokeActions($result)
    {
        $error = EngineError::getLastError();
        $this->assignSelfState(true, $error, $result);

        if (false !== $this->restoreReportingLevel) {
            Silencer::restore();
        }
    }

    /**
     * @param bool         $invoked
     * @param mixed[]|null $raisedError
     * @param mixed        $result
     */
    private function assignSelfState(bool $invoked, array $raisedError = null, $result)
    {
        list($this->called, $this->raisedError, $this->result) =
            [$invoked, $raisedError, $result];
    }
}

/* EOF */
