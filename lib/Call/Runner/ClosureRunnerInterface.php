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

interface ClosureRunnerInterface
{
    /**
     * @param \Closure    $closure
     * @param null|object $binding
     *
     * @return ClosureRunnerInterface
     */
    public static function create(\Closure $closure, $binding = null) : ClosureRunnerInterface;

    /**
     * @param mixed $result
     * @param mixed $errors
     * @param array ...$parameters
     */
    public function invoke(&$result, &$errors, ...$parameters);

    /**
     * @return \mixed[]|null
     */
    public static function actionsAfter();
}

/* EOF */
