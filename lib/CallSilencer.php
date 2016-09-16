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

use SR\Silencer\Exception\InvocationException;
use SR\Silencer\Exception\ResultValidatorException;

/**
 * Implementation for calling closure in error-silenced environment.
 */
class CallSilencer implements CallSilencerInterface
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var \Closure|null
     */
    private $validator;

    /**
     * @var mixed
     */
    private $return;

    /**
     * @var string[]|null
     */
    private $raisedError;

    /**
     * Static method to create class instance with optional closure and validator parameters.
     *
     * @param \Closure|null $closure   A closure to call in silenced environment
     * @param \Closure|null $validator A closure to call during return value validation
     *
     * @return static
     */
    public static function create(\Closure $closure = null, \Closure $validator = null)
    {
        $instance = new static();

        if ($closure) {
            $instance->setClosure($closure);
        }

        if ($validator) {
            $instance->setValidator($validator);
        }

        return $instance;
    }

    /**
     * Sets the closure to invoke.
     *
     * @param \Closure $closure A closure to call in silenced environment
     *
     * @return $this
     */
    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;

        return $this;
    }

    /**
     * Sets the closure used to test validity of return value. When called, this closure is provided the return value
     * of the invoked closure, as well the last raised error array (as first and second parameters).
     *
     * @param \Closure $validator A closure to call during return value validation using {@see isReturnValid()}
     *
     * @return $this
     */
    public function setValidator(\Closure $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Invoke closure in silenced environment.
     *
     * @param bool $restore Calls {@see Silencer::restore()} to restore error level after calling closure if true
     *
     * @throws InvocationException If closure throws exception
     *
     * @return $this
     */
    public function invoke($restore = true)
    {
        if (null === $invokable = $this->closure) {
            throw new InvocationException('Cannot call undefined. Set closure before calling invoke.');
        }

        if (!Silencer::isSilenced()) {
            Silencer::silence();
        }

        error_clear_last();

        try {
            $this->return = $invokable();
        } catch (\Exception $e) {
            throw new InvocationException('Exception thrown while silently invoking closure.', $e);
        } finally {
            $this->raisedError = error_get_last();

            if ($restore) {
                Silencer::restore();
            }
        }

        return $this;
    }

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult()
    {
        return $this->return !== null;
    }

    /**
     * Get the return value invoked closure.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->return;
    }

    /**
     * Returns type hinted value from validation closure.
     *
     * @throws ResultValidatorException If no validator closure has been assigned
     *
     * @return bool
     */
    public function isResultValid()
    {
        if (null === $closure = $this->validator) {
            throw new ResultValidatorException('Unable to validate result if validator closure is unset.');
        }

        return $closure($this->return, $this->raisedError);
    }

    /**
     * Returns true if the invoked closure return value is true (strict check).
     *
     * @return bool
     */
    public function isResultTrue()
    {
        return $this->return === true;
    }

    /**
     * Returns true if the invoked closure return value is false (strict check).
     *
     * @return bool
     */
    public function isResultFalse()
    {
        return $this->return === false;
    }

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->raisedError !== null;
    }

    /**
     * Returns the error raised by invoking closure.
     *
     * @param string|null $index The index string to get from last error array, or return original error array
     *
     * @return string[]|string|bool
     */
    public function getError($index = self::ERROR_ARRAY)
    {
        if (!$this->raisedError) {
            return false;
        }

        if ($index === self::ERROR_ARRAY) {
            return $this->raisedError;
        }

        if (!isset($this->raisedError[$index])) {
            return false;
        }

        return $this->raisedError[$index];
    }
}

/* EOF */
