<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Call\Define;

use SR\Silencer\Call\Result\ResultInfoInterface;

/**
 * Interface for calling closure in error-silenced context.
 */
interface DefinitionInterface
{
    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     * @param object        $invokableBind Binding for main invokable closure call
     * @param object        $validatorBind Binding for result validation closure
     */
    public function __construct(\Closure $invokableInst = null, \Closure $validatorInst = null, $invokableBind = null, $validatorBind = self::class);

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     *
     * @return static|DefinitionInterface
     */
    public static function create(\Closure $invokableInst = null, \Closure $validatorInst = null) : DefinitionInterface;

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $invokableInst A closure to call in silenced environment
     *
     * @return DefinitionInterface
     */
    public function setInvokable(\Closure $invokableInst = null) : DefinitionInterface;

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validatorInst An instance of \Closure called to determine validity of return value and/or raised error
     *
     * @return DefinitionInterface
     */
    public function setValidator(\Closure $validatorInst = null) : DefinitionInterface;

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     *
     * @throws \Exception If an exception is thrown within the \Closure instance
     *
     * @return \SR\Silencer\Call\Result\ResultInfoInterface
     */
    public function invoke(...$parameters) : ResultInfoInterface;

    /**
     * Get the return value invoked closure.
     *
     * @return \SR\Silencer\Call\Result\ResultInfoInterface
     */
    public function getResult() : ResultInfoInterface;

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult() : bool;
}

/* EOF */
