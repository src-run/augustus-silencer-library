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

use SR\Silencer\Silencer;
use SR\Silencer\Util\PhpError;

final class ClosureRunner implements ClosureRunnerInterface
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var object
     */
    private $binding;

    /**
     * @param \Closure    $closure
     * @param null|object $binding
     */
    public function __construct(\Closure $closure, $binding = null)
    {
        $this->closure = $closure;
        $this->binding = $binding;
    }

    /**
     * @param \Closure    $closure
     * @param null|object $binding
     *
     * @return ClosureRunnerInterface
     */
    public static function create(\Closure $closure, $binding = null) : ClosureRunnerInterface
    {
        return new static($closure, $binding);
    }

    /**
     * @param mixed $result
     * @param mixed $errors
     * @param array ...$parameters
     */
    public function invoke(&$result, &$errors, ...$parameters)
    {
        static::actionsPrior();
        $caller = $this->binding ? $this->closure->bindTo($this->binding, $this->binding) : $this->closure;
        $result = $caller(...$parameters);
        $errors = static::actionsAfter();
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
        Silencer::restore();

        return PhpError::getLastError();
    }
}

/* EOF */
