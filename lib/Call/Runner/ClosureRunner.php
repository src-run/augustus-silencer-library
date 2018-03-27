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

final class ClosureRunner
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
     * @param \Closure|null $closure
     * @param object|null   $binding
     */
    public function __construct(\Closure $closure = null, $binding = null)
    {
        $this->closure = $closure;
        $this->binding = $binding;
    }

    /**
     * @param mixed ...$parameters
     *
     * @throws \RuntimeException
     *
     * @return mixed[]
     */
    public function run(...$parameters): array
    {
        static::silence();

        if ($this->binding) {
            $this->closure->bindTo($this->binding, $this->binding);
        }

        $result = null;

        try {
            $result = ($this->closure)(...$parameters);
        } catch (\Exception $exception) {
            throw new \RuntimeException(sprintf('Silenced call runner error: %s', $exception->getMessage()), 0, $exception);
        } finally {
            $raised = static::restore();
        }

        return [$result, $raised ?? static::restore()];
    }

    /**
     * @return array|null
     */
    private static function restore(): ?array
    {
        if (Silencer::isSilenced() && Silencer::hasPriorState()) {
            Silencer::restore();
        }

        return error_get_last();
    }

    private static function silence(): void
    {
        error_clear_last();

        if (!Silencer::isSilenced()) {
            Silencer::silence();
        }
    }
}
