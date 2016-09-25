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

/**
 * Interface for calling closure in error-silenced context.
 */
interface CallSilencerInterface
{
    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokable Closure instance called in silenced environment
     * @param \Closure|null $validator Optional closure that determines validity of return value
     * @param object        $bind      Optional binding context to apply to closures
     */
    public function __construct(\Closure $invokable = null, \Closure $validator = null, $bind = null);

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokable Closure instance called in silenced environment
     * @param \Closure|null $validator Optional closure that determines validity of return value
     * @param object        $bind      Optional binding context to apply to closures
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $invokable = null, \Closure $validator = null, $bind = null) : CallSilencerInterface;

    /**
     * Disables restoring error reporting level after invoking silenced closure.
     *
     * @return CallSilencerInterface
     */
    public function disableSilencerRestoration() : CallSilencerInterface;

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $invokable A closure to call in silenced environment
     * @param object   $bind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setInvokable(\Closure $invokable = null, $bind = null) : CallSilencerInterface;

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validator An instance of \Closure called to determine validity of return value and/or raised error
     * @param object   $bind      Optional binding context to apply to closure when called
     *
     * @return CallSilencerInterface
     */
    public function setValidator(\Closure $validator = null, $bind = null) : CallSilencerInterface;

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     *
     * @throws \Exception If an exception is thrown within the \Closure instance.
     *
     * @return CallSilencerInterface
     */
    public function invoke(...$parameters) : CallSilencerInterface;

    /**
     * Returns true if the closure was called.
     *
     * @return bool
     */
    public function isInvoked() : bool;

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult() : bool;

    /**
     * Get the return value invoked closure.
     *
     * @return mixed
     */
    public function getResult();

    /**
     * Returns equality of passed value to result.
     *
     * @param mixed $what
     *
     * @return bool
     */
    public function isResult($what) : bool;

    /**
     * Returns true if the invoked closure return value is true (strict check).
     *
     * @return bool
     */
    public function isResultTrue() : bool;

    /**
     * Returns true if the invoked closure return value is false (strict check).
     *
     * @return bool
     */
    public function isResultFalse() : bool;

    /**
     * Returns type hinted value from validation closure.
     *
     * @return bool
     */
    public function isResultValid() : bool;

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError() : bool;

    /**
     * @param string|null $index
     *
     * @return string|int|mixed[]
     */
    public function getError(string $index = null);

    /**
     * Return the error message caused by a call in the invoked closure.
     *
     * @return string
     */
    public function getErrorMessage();

    /**
     * Return the error type integer caused by a call in the invoked closure.
     *
     * @return int
     */
    public function getErrorType();
}

/* EOF */
