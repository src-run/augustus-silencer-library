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

interface ResultInspectorInterface
{
    /**
     * @param \Closure|null $validator
     * @param object        $binding
     */
    public function __construct(\Closure $validator = null, $binding = null);

    /**
     * @param \Closure $validator
     * @param object   $binding
     *
     * @return ResultInspectorInterface
     */
    public function setValidator(\Closure $validator = null, $binding = null) : ResultInspectorInterface;

    /**
     * @param mixed      $result
     * @param array|null $raised
     * @param bool       $called
     *
     * @return ResultInspectorInterface
     */
    public function setResult($result, array $raised = null, $called = true) : ResultInspectorInterface;

    /**
     * @return bool
     */
    public function isCalled() : bool;

    /**
     * @return mixed
     */
    public function getReturn();

    /**
     * @return bool
     */
    public function hasReturn() : bool;

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
