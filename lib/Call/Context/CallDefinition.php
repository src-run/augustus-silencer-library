<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Call\Context;

use SR\Silencer\Call\Result\ResultInfo;
use SR\Silencer\Call\Result\ResultInfoInterface;
use SR\Silencer\Call\Runner\ClosureRunner;

/**
 * Simple API for calling closure in error-silenced context.
 */
final class CallDefinition implements CallDefinitionInterface
{
    /**
     * Closure instance invoked in silenced environment.
     *
     * @var \Closure
     */
    private $closure;

    /**
     * Alternate object context to bind closure to.
     *
     * @var object
     */
    private $binding;

    /**
     * Returning value of invoked closure.
     *
     * @var ResultInfo
     */
    private $result;

    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $closure          Main invokable called in an error-silenced environment
     * @param \Closure|null $validator        Validation checker that determines return value validity
     * @param object        $closureBinding   Binding for main invokable closure call
     * @param object        $validatorBinding Binding for result validation closure
     */
    public function __construct(\Closure $closure = null, \Closure $validator = null, $closureBinding = null, $validatorBinding = null)
    {
        $this->setInvokable($closure, $closureBinding);
        $this->setValidator($validator, $validatorBinding);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $closure   Main invokable called in an error-silenced environment
     * @param \Closure|null $validator Validation checker that determines return value validity
     *
     * @return static|CallDefinitionInterface
     */
    public static function create(\Closure $closure = null, \Closure $validator = null) : CallDefinitionInterface
    {
        return new static($closure, $validator);
    }

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $closure A closure to call in silenced environment
     * @param object   $binding Optional binding context to apply to closure when called
     *
     * @return CallDefinitionInterface
     */
    public function setInvokable(\Closure $closure = null, $binding = null) : CallDefinitionInterface
    {
        $this->closure = $closure;
        $this->binding = $binding ?: $this->binding;

        return $this;
    }

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validator An instance of \Closure called to determine validity of return value and/or raised error
     * @param object   $binding   Optional binding context to apply to closure when called
     *
     * @return CallDefinitionInterface
     */
    public function setValidator(\Closure $validator = null, $binding = null) : CallDefinitionInterface
    {
        $this->result = new ResultInfo($validator, $binding ?: $binding);

        return $this;
    }

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     *
     * @throws \Exception If an exception is thrown within the \Closure instance
     *
     * @return ResultInfoInterface
     */
    public function invoke(...$parameters) : ResultInfoInterface
    {
        if (!$this->closure) {
            return $this->result;
        }

        $raised = null;
        $result = ClosureRunner::create()
            ->setInvokable($this->closure, $this->binding)
            ->runInvokable(...$parameters);

        return $this->result->setResult(...$result);
    }

    /**
     * Get the return value invoked closure.
     *
     * @return \SR\Silencer\Call\Result\ResultInfoInterface
     */
    public function getResult() : ResultInfoInterface
    {
        return $this->result;
    }
}

/* EOF */
