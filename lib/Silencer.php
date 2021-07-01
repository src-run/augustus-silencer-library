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

final class Silencer
{
    /**
     * @var int
     */
    private const NEGATIVE_SILENCE_MASK = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED | E_STRICT;

    /**
     * @var int[]
     */
    private static $reportingLevelHistory = [];

    /**
     * Enter new silenced error reporting level with optionally custom level mask.
     *
     * @param int|null $mask The new error mask to apply
     */
    public static function silence(int $mask = null): int
    {
        static::unShiftLevelHistory(static::getErrorReporting());

        return static::setErrorReporting(
            static::getSilencedMask(static::priorLevelHistory(), $mask)
        );
    }

    /**
     * Enter silenced error reporting level if not already in silenced state.
     *
     * @param int|null $mask The new error mask to apply
     */
    public static function silenceIfNot(int $mask = null): int
    {
        if (!static::isSilenced()) {
            static::silence($mask);
        }

        return static::getErrorReporting();
    }

    /**
     * Restore previous error reporting level assigned through call to {@see Silencer::silence}.
     */
    public static function restore(): int
    {
        if (!static::hasPriorState()) {
            return static::getErrorReporting();
        }

        return static::setErrorReporting(static::shiftLevelHistory());
    }

    /**
     * Returns true if if current error reporting level equals the default silenced level.
     */
    public static function isSilenced(): bool
    {
        if (!static::hasPriorState()) {
            return false;
        }

        return static::getSilencedMask(static::priorLevelHistory()) ===
               static::getSilencedMask(static::getErrorReporting());
    }

    /**
     * Returns true if state is "restorable" (meaning a prior restore state exists).
     */
    public static function hasPriorState(): bool
    {
        return (bool) count(static::$reportingLevelHistory) > 0;
    }

    private static function unShiftLevelHistory(int $level): int
    {
        array_unshift(static::$reportingLevelHistory, $level);

        return $level;
    }

    private static function shiftLevelHistory(): int
    {
        return array_shift(static::$reportingLevelHistory);
    }

    private static function priorLevelHistory(): int
    {
        return static::hasPriorState() ? static::$reportingLevelHistory[0] : static::getErrorReporting();
    }

    /**
     * @param int|null $level
     */
    private static function setErrorReporting(int $level): int
    {
        error_reporting($level);

        return static::getErrorReporting();
    }

    private static function getErrorReporting(): int
    {
        return error_reporting();
    }

    private static function getSilencedMask(int $mask, int $subtract = null): int
    {
        return $mask & ~($subtract ?: self::NEGATIVE_SILENCE_MASK);
    }
}
