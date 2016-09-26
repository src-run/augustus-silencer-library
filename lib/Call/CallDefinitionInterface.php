<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Call;

use SR\Silencer\Call\Result\ResultInspectorInterface;

interface CallDefinitionInterface
{
    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $closure          Main invokable called in an error-silenced environment
     * @param \Closure|null $validator        Validation checker that determines return value validity
     * @param object        $closureBinding   Binding for main invokable closure call
     * @param object        $validatorBinding Binding for result validation closure
     */
    public function __construct(\Closure $closure = null, \Closure $validator = null, $closureBinding = null, $validatorBinding = self::class);

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $closure   Main invokable called in an error-silenced environment
     * @param \Closure|null $validator Validation checker that determines return value validity
     *
     * @return static|CallDefinitionInterface
     */
    public static function create(\Closure $closure = null, \Closure $validator = null) : CallDefinitionInterface;

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $closure A closure to call in silenced environment
     *
     * @return CallDefinitionInterface
     */
    public function setInvokable(\Closure $closure = null) : CallDefinitionInterface;

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validator An instance of \Closure called to determine validity of return value and/or raised error
     *
     * @return CallDefinitionInterface
     */
    public function setValidator(\Closure $validator = null) : CallDefinitionInterface;

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     *
     * @throws \Exception If an exception is thrown within the \Closure instance
     *
     * @return \SR\Silencer\Call\Result\ResultInspectorInterface
     */
    public function invoke(...$parameters) : ResultInspectorInterface;

    /**
     * Get the return value invoked closure.
     *
     * @return \SR\Silencer\Call\Result\ResultInspectorInterface
     */
    public function getResult() : ResultInspectorInterface;
}
