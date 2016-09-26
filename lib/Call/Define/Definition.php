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

use SR\Silencer\Call\Result\ResultInfo;
use SR\Silencer\Call\Result\ResultInfoInterface;
use SR\Silencer\Call\Runner\ClosureRunner;

/**
 * Simple API for calling closure in error-silenced context.
 */
final class Definition implements DefinitionInterface
{
    /**
     * Closure instance invoked in silenced environment.
     *
     * @var \Closure
     */
    private $invokableInst;

    /**
     * Alternate object context to bind closure to.
     *
     * @var object
     */
    private $invokableBind;

    /**
     * Closure instance used to determine validity of main closure call.
     *
     * @var \Closure
     */
    private $validatorInst;

    /**
     * Alternate object context to bind validation closure to.
     *
     * @var object
     */
    private $validatorBind;

    /**
     * Returning value of invoked closure.
     *
     * @var mixed
     */
    private $result;

    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     * @param object        $invokableBind Binding for main invokable closure call
     * @param object        $validatorBind Binding for result validation closure
     */
    public function __construct(\Closure $invokableInst = null, \Closure $validatorInst = null, $invokableBind = null, $validatorBind = null)
    {
        $this->setInvokable($invokableInst, $invokableBind);
        $this->setValidator($validatorInst, $validatorBind);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     *
     * @return static|DefinitionInterface
     */
    public static function create(\Closure $invokableInst = null, \Closure $validatorInst = null) : DefinitionInterface
    {
        return new static($invokableInst, $validatorInst);
    }

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure $invokableInst A closure to call in silenced environment
     * @param object   $invokableBind Optional binding context to apply to closure when called
     *
     * @return DefinitionInterface
     */
    public function setInvokable(\Closure $invokableInst = null, $invokableBind = null) : DefinitionInterface
    {
        $this->invokableInst = $invokableInst;
        $this->invokableBind = $invokableBind ?: $this->invokableBind;

        return $this;
    }

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure $validatorInst An instance of \Closure called to determine validity of return value and/or raised error
     * @param object   $validatorBind Optional binding context to apply to closure when called
     *
     * @return DefinitionInterface
     */
    public function setValidator(\Closure $validatorInst = null, $validatorBind = null) : DefinitionInterface
    {
        $this->validatorInst = $validatorInst;
        $this->validatorBind = $validatorBind ?: $this->validatorBind;

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
        $invalid = ResultInfo::create(false, null, false);

        if (!$this->invokableInst) {
            return $invalid;
        }

        $result = $error = null;

        try {
            $runner = ClosureRunner::create($this->invokableInst, $this->invokableBind);
            $runner->invoke($result, $error, ...$parameters);
        } catch (\Exception $exception) {
            throw new \RuntimeException('An exception was thrown inside closure.', 0, $exception);
        } finally {
            if ($finalError = ClosureRunner::actionsAfter()) {
                $error = $finalError;
            }
        }

        return $this->result = ResultInfo::create($result, $error, true, $this->validatorInst, $this->validatorBind);
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

    /**
     * Returns true if a non-null value was returned from invoked closure.
     *
     * @return bool
     */
    public function hasResult() : bool
    {
        return $this->result instanceof ResultInfoInterface;
    }
}

/* EOF */
