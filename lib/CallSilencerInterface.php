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
     * Optional constant parameter for {@see getError()} to get error as original array.
     */
    const ERROR_ARRAY = 'array';

    /**
     * Optional constant parameter for {@see getError()} to get error message.
     */
    const ERROR_MESSAGE = 'message';

    /**
     * Optional constant parameter for {@see getError()} to get error type.
     */
    const ERROR_TYPE = 'type';

    /**
     * Optional constant parameter for {@see getError()} to get error file.
     */
    const ERROR_FILE = 'file';

    /**
     * Optional constant parameter for {@see getError()} to get error line.
     */
    const ERROR_LINE = 'line';

    /**
     * Static method to create class instance with optional closure and validator parameters.
     *
     * @param \Closure|null $closure   A closure to call in silenced environment
     * @param \Closure|null $validator A closure to call during return value validation
     *
     * @return static
     */
    public static function create(\Closure $closure = null, \Closure $validator = null);

    /**
     * Sets the closure to invoke.
     *
     * @param \Closure $invokable A closure to call in silenced environment
     *
     * @return $this
     */
    public function setClosure(\Closure $invokable);

    /**
     * Sets the closure used to test validity of return value. When called, this closure is provided the return value
     * of the invoked closure, as well the last raised error array (as first and second parameters).
     *
     * @param \Closure $validator A closure to call during return value validation using {@see isReturnValid()}
     *
     * @return $this
     */
    public function setValidator(\Closure $validator);

    /**
     * Invoke closure in silenced environment.
     *
     * @param bool $restore Calls {@see Silencer::restore()} to restore error level after calling closure if true
     *
     * @return $this
     */
    public function invoke($restore = true);

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult();

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
    public function isResultValid();

    /**
     * Returns true if the invoked closure return value is true (strict check).
     *
     * @return bool
     */
    public function isResultTrue();

    /**
     * Returns true if the invoked closure return value is false (strict check).
     *
     * @return bool
     */
    public function isResultFalse();

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError();

    /**
     * Returns the error raised by invoking closure.
     *
     * @param string|null $index The index string to get from last error array, or return original error array
     *
     * @return string[]|string
     */
    public function getError($index = self::ERROR_MESSAGE);
}

/* EOF */
