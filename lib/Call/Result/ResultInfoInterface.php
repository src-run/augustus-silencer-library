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

interface ResultInfoInterface
{
    /**
     * @param mixed         $result
     * @param mixed[]|null  $raised
     * @param bool          $called
     * @param \Closure|null $validatorInst
     * @param object        $validatorBind
     */
    public function __construct($result, array $raised = null, $called = true, \Closure $validatorInst = null, $validatorBind = null);

    /**
     * @param mixed         $result
     * @param mixed[]|null  $raised
     * @param bool          $called
     * @param \Closure|null $validatorInst
     * @param object        $validatorBind
     *
     * @return ResultInfoInterface
     */
    public static function create($result, array $raised = null, $called = true, \Closure $validatorInst = null, $validatorBind = null) : ResultInfoInterface;

    /**
     * @param \Closure $validatorInst
     * @param object   $validatorBind
     *
     * @return ResultInfoInterface
     */
    public function setValidator(\Closure $validatorInst = null, $validatorBind = null) : ResultInfoInterface;

    /**
     * Returns true if the closure was called.
     *
     * @return bool
     */
    public function isCalled() : bool;

    /**
     * @return mixed
     */
    public function get();

    /**
     * @return mixed
     */
    public function getResult();

    /**
     * @return bool
     */
    public function has() : bool;

    /**
     * @param mixed $comparison
     *
     * @return bool
     */
    public function isEquitable($comparison) : bool;

    /**
     * @return bool
     */
    public function isTrue() : bool;

    /**
     * @return bool
     */
    public function isFalse() : bool;

    /**
     * @return bool
     */
    public function isValid() : bool;

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
