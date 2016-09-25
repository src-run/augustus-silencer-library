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

/**
 * Engine error reporting silencing/restoring API.
 */
final class Silencer implements SilencerInterface
{
    /**
     * An array of error reporting levels. Multiple calls to {@see Silencer::silence()} will shift new error reporting
     * levels onto the beginning of this array. Subsequent calls to {@see Silencer::restore()} will unshift these saved
     * error reporting levels and re-apply them.
     *
     * @var int[]
     */
    private static $reportingLevelHistory = [];

    /**
     * Enter new silenced error reporting level with optionally custom level mask.
     *
     * @param int|null $mask The new error mask to apply
     *
     * @return int
     */
    public static function silence(int $mask = null) : int
    {
        static::unShiftLevelHistory(static::getErrorReporting());

        return static::setErrorReporting(
            static::getSilencedMask(static::priorLevelHistory(), $mask)
        );
    }

    /**
     * Enter silenced error reporting level if not already in silenced state.
     *
     * @param int|null $mask The new errir mask to apply.
     *
     * @return int
     */
    public static function silenceIfNot(int $mask = null) : int
    {
        if (!static::isSilenced()) {
            static::silence($mask);
        }

        return static::getErrorReporting();
    }

    /**
     * Restore previous error reporting level assigned through call to {@see Silencer::silence}.
     *
     * @return int
     */
    public static function restore() : int
    {
        if (!static::hasLevelHistory()) {
            return static::getErrorReporting();
        }

        return static::setErrorReporting(static::shiftLevelHistory());
    }

    /**
     * Returns true if if current error reporting level equals the default silenced level.
     *
     * @return bool
     */
    public static function isSilenced() : bool
    {
        if (!static::hasLevelHistory()) {
            return false;
        }

        return static::getSilencedMask(static::priorLevelHistory()) ===
               static::getSilencedMask(static::getErrorReporting());
    }

    /**
     * Returns true if state is "restorable" (meaning a prior restore state exists).
     *
     * @return bool
     */
    public static function isRestorable() : bool
    {
        return static::hasLevelHistory();
    }

    /**
     * Returns true if prior restore state exists.
     *
     * @return bool
     */
    private static function hasLevelHistory() : bool
    {
        return (bool) count(static::$reportingLevelHistory) > 0;
    }

    /**
     * @param int $level
     *
     * @return int
     */
    private static function unShiftLevelHistory(int $level) : int
    {
        array_unshift(static::$reportingLevelHistory, $level);

        return $level;
    }

    /**
     * @return int
     */
    private static function shiftLevelHistory() : int
    {
        return array_shift(static::$reportingLevelHistory);
    }

    /**
     * @return int
     */
    private static function priorLevelHistory()
    {
        return static::hasLevelHistory() ? static::$reportingLevelHistory[0] : static::getErrorReporting();
    }

    /**
     * @param int|null $level
     *
     * @return int
     */
    private static function setErrorReporting(int $level) : int
    {
        error_reporting($level);

        return static::getErrorReporting();
    }

    /**
     * @return int
     */
    private static function getErrorReporting() : int
    {
        return error_reporting();
    }

    /**
     * @param int      $mask
     * @param int|null $subtract
     *
     * @return int
     */
    private static function getSilencedMask(int $mask, int $subtract = null) : int
    {
        return $mask & ~($subtract ?: self::NEGATIVE_SILENCE_MASK);
    }
}

/* EOF */
