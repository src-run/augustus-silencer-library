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
 * Implementation for calling closure in error-silenced environment.
 */
class CallSilencer implements CallSilencerInterface
{
    /**
     * The closure instance to invoke.
     *
     * @var \Closure
     */
    private $closure;

    /**
     * Optional validation closure that is invoked to check result validity.
     *
     * @var \Closure|null
     */
    private $validator;

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
     * True if closure has been invoked.
     *
     * @var bool
     */
    private $invoked;

    /**
     * Static method to create class instance with optional closure and validator parameters.
     *
     * @param \Closure|null $closure   A closure to call in silenced environment
     * @param \Closure|null $validator A closure to call during return value validation
     */
    public function __construct(\Closure $closure = null, \Closure $validator = null)
    {
        if ($closure) {
            $this->setClosure($closure);
        }

        if ($validator) {
            $this->setValidator($validator);
        }
    }

    /**
     * Static method to create class instance with optional closure and validator parameters.
     *
     * @param \Closure|null $closure   A closure to call in silenced environment
     * @param \Closure|null $validator A closure to call during return value validation
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $closure = null, \Closure $validator = null) : CallSilencerInterface
    {
        return new static($closure, $validator);
    }

    /**
     * Sets the closure to invoke.
     *
     * @param \Closure $closure A closure to call in silenced environment
     *
     * @return CallSilencerInterface
     */
    public function setClosure(\Closure $closure) : CallSilencerInterface
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
     * @return CallSilencerInterface
     */
    public function setValidator(\Closure $validator) : CallSilencerInterface
    {
        $this->validator = $validator;

        return $this;
    }

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
    public function invoke($restore = true) : CallSilencerInterface
    {
        if (null === $this->closure) {
            return $this;
        }

        if (!Silencer::isSilenced()) {
            Silencer::silence();
        }

        error_clear_last();

        try {
            $this->result = ($this->closure)();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $this->raisedError = error_get_last();
            $this->invoked = true;

            if ($restore) {
                Silencer::restore();
            }
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
     * Returns type hinted value from validation closure.
     *
     * @return bool
     */
    public function isResultValid() : bool
    {
        if (null === $this->validator) {
            return !$this->hasError();
        }

        return (bool) ($this->validator)($this->result, $this->raisedError);
    }

    /**
     * Returns true if the invoked closure return value is true (strict check).
     *
     * @return bool
     */
    public function isResultTrue() : bool
    {
        return $this->result === true;
    }

    /**
     * Returns true if the invoked closure return value is false (strict check).
     *
     * @return bool
     */
    public function isResultFalse() : bool
    {
        return $this->result === false;
    }

    /**
     * Return true if an error was raised by invoking closure.
     *
     * @return bool
     */
    public function hasError() : bool
    {
        return null !== $this->raisedError && isset($this->raisedError['message']);
    }

    /**
     * Return the error message caused by a call in the invoked closure.
     *
     * @return string
     */
    public function getErrorMessage() : string
    {
        return (string) $this->getErrorIndex('message') ?: '';
    }

    /**
     * Return the error type integer caused by a call in the invoked closure.
     *
     * @return int
     */
    public function getErrorType() : int
    {
        return (string) $this->getErrorIndex('type') ?: 0;
    }

    /**
     * @param string $index
     *
     * @return string
     */
    private function getErrorIndex(string $index)
    {
        return isset($this->raisedError[$index]) ? $this->raisedError[$index] : null;
    }
}

/* EOF */
