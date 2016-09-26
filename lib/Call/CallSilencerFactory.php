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

use SR\Silencer\Call\Context\CallDefinition;

final class CallSilencerFactory implements CallSilencerFactoryInterface
{
    /**
     * Static method constructs method creates a new call definition using the provided parameters.
     *
     * @param \Closure|null $invokableInst A closure instance containing logic that should be invoked in silenced context
     * @param \Closure|null $validatorInst A closure instance that determines validity of silenced code return value
     * @param object|null   $invokableBind Alternative bind scope to apply to the silenced closure
     * @param object|null   $validatorBind Alternative bind scope to apply to validation closure
     *
     * @return CallDefinition
     */
    public static function create(\Closure $invokableInst = null, \Closure $validatorInst = null, $invokableBind = null, $validatorBind = null) : CallDefinition
    {
        $definition = CallDefinition::create();
        $definition->setInvokable($invokableInst, $invokableBind);
        $definition->setValidator($validatorInst, $validatorBind);

        return $definition;
    }
}

/* EOF */
