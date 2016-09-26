<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Call\Runner;

use Gitonomy\Git\Exception\RuntimeException;
use SR\Silencer\Silencer;
use SR\Silencer\Util\PhpError;

final class ClosureRunner implements ClosureRunnerInterface
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
     * @param \Closure $closure
     * @param object   $binding
     *
     * @return ClosureRunnerInterface
     */
    public static function create(\Closure $closure = null, $binding = null) : ClosureRunnerInterface
    {
        $runner = new static();
        $runner->setInvokable($closure, $binding);

        return $runner;
    }

    /**
     * @param \Closure $closure
     * @param object   $binding
     *
     * @return ClosureRunnerInterface
     */
    public function setInvokable(\Closure $closure = null, $binding = null) : ClosureRunnerInterface
    {
        $this->closure = $closure;
        $this->binding = $binding ?: $this->binding;

        return $this;
    }

    /**
     * @param mixed ...$parameters
     *
     * @throws \Exception
     *
     * @return mixed[]
     */
    public function runInvokable(...$parameters)
    {
        static::actionsPrior();

        $toCall = $this->closure;

        if ($this->binding) {
            $toCall->bindTo($this->binding, $this->binding);
        }

        $return = $thrown = null;

        try {
            $return = $toCall(...$parameters);
        } catch (\Exception $exception) {
            throw new RuntimeException(sprintf('Silenced call runner error: %s', $exception->getMessage()), 0, $exception);
        } finally {
            $raised = static::actionsAfter();
        }

        return [$return, isset($raised) ? $raised : static::actionsAfter(), true];
    }

    /**
     * Clear error stack and silence if needed.
     */
    public static function actionsPrior()
    {
        PhpError::clearLastError();

        if (!Silencer::isSilenced()) {
            Silencer::silence();
        }
    }

    /**
     * @return \mixed[]|null
     */
    public static function actionsAfter()
    {
        if (Silencer::isSilenced() && Silencer::isRestorable()) {
            Silencer::restore();
        }

        return PhpError::getLastError();
    }
}

/* EOF */
