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

use SR\Silencer\Call\Result\ResultInspector;
use SR\Silencer\Call\Runner\ClosureRunner;

final class CallDefinition
{
    private ?\Closure $closure = null;

    private ?object $binding = null;

    private ResultInspector $inspector;

    /**
     * Constructor allows for setting main closure or validation closure.
     *
     * @param \Closure|null $invokable       Main invokable called in an error-silenced environment
     * @param \Closure|null $validator       Validation checker that determines return value validity
     * @param object|null   $bindToInvokable Binding for main invokable closure call
     * @param object|null   $bindToValidator Binding for result validation closure
     */
    public function __construct(\Closure $invokable = null, \Closure $validator = null, object $bindToInvokable = null, object $bindToValidator = null)
    {
        $this->setInvokable($invokable, $bindToInvokable);
        $this->setValidator($validator, $bindToValidator);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokable Main invokable called in an error-silenced environment
     * @param \Closure|null $validator Validation checker that determines return value validity
     *
     * @return static|self
     */
    public static function create(\Closure $invokable = null, \Closure $validator = null): self
    {
        return new self($invokable, $validator);
    }

    /**
     * Assigns a \Closure instance that will be called in error silenced environment.
     *
     * @param \Closure|null $closure A closure to call in silenced environment
     * @param object|null   $binding Optional binding context to apply to closure when called
     */
    public function setInvokable(\Closure $closure = null, $binding = null): self
    {
        $this->closure = $closure;
        $this->binding = $binding ?: $this->binding;

        return $this;
    }

    /**
     * Assigns a \Closure instance used to determine return value validity. It is passed the return value and the php
     * error array (or null if non exists) as its only parameters.
     *
     * @param \Closure|null $validator An instance of \Closure called to determine validity of return value and/or raised error
     * @param object|null   $binding   Optional binding context to apply to closure when called
     */
    public function setValidator(\Closure $validator = null, object $binding = null): self
    {
        $this->inspector = new ResultInspector($validator, $binding);

        return $this;
    }

    /**
     * Invoke the closure within a silenced environment.
     *
     * @param mixed ...$parameters Any parameters to call to invoked closure
     */
    public function invoke(...$parameters): ResultInspector
    {
        if ($this->closure) {
            $this->inspector->setReturn(...(new ClosureRunner($this->closure, $this->binding))->run(...$parameters));
        }

        return $this->inspector;
    }

    /**
     * Get the return value invoked closure.
     */
    public function getResult(): ResultInspector
    {
        return $this->inspector;
    }
}
