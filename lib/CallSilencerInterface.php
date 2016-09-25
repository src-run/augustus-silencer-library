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
 * Interface for calling closure in error-silenced environment.
 */
interface CallSilencerInterface
{
    /**
     * Static method to create class instance with optional closure and validator parameters.
     *
     * @param \Closure|null $closure   A closure to call in silenced environment
     * @param \Closure|null $validator A closure to call during return value validation
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $closure = null, \Closure $validator = null) : CallSilencerInterface;

    /**
     * Sets the closure to invoke.
     *
     * @param \Closure $closure A closure to call in silenced environment
     *
     * @return CallSilencerInterface
     */
    public function setClosure(\Closure $closure) : CallSilencerInterface;

    /**
     * Sets the closure used to test validity of return value. When called, this closure is provided the return value
     * of the invoked closure, as well the last raised error array (as first and second parameters).
     *
     * @param \Closure $validator A closure to call during return value validation using {@see isReturnValid()}
     *
     * @return CallSilencerInterface
     */
    public function setValidator(\Closure $validator) : CallSilencerInterface;

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param bool $restore Restores prior error level after silencing and invoking closire. If false, the silenced
     *                      error level will remain indefinably.
     *
     * @throws \Exception If an exception is thrown within the \Closure instance.
     *
     * @return CallSilencerInterface
     */
    public function invoke($restore = true) : CallSilencerInterface;

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
     * Returns type hinted value from validation closure.
     *
     * @return bool
     */
    public function isResultValid() : bool;

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
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError() : bool;

    /**
     * Return the error message caused by a call in the invoked closure.
     *
     * @return string
     */
    public function getErrorMessage() : string;

    /**
     * Return the error type integer caused by a call in the invoked closure.
     *
     * @return int
     */
    public function getErrorType() : int;
}

/* EOF */
