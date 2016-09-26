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

use SR\Silencer\Call\Define\Definition;

/**
 * Simple API for calling closure in error-silenced context.
 */
final class CallSilencer implements CallSilencerInterface
{
    /**
     * @var Definition
     */
    private $definition;

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
        $this->definition = Definition::create();
        $this->definition->setInvokable($invokableInst, $invokableBind);
        $this->definition->setValidator($validatorInst, $validatorBind);
    }

    /**
     * Static method constructs method using same options as main constructor.
     *
     * @param \Closure|null $invokableInst Main invokable called in an error-silenced environment
     * @param \Closure|null $validatorInst Validation checker that determines return value validity
     *
     * @return static|CallSilencerInterface
     */
    public static function create(\Closure $invokableInst = null, \Closure $validatorInst = null) : CallSilencerInterface
    {
        return new static($invokableInst, $validatorInst);
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return Definition
     */
    public function __call($name, $arguments)
    {
        return $this->definition->{$name}(...$arguments);
    }
}

/* EOF */
