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

use SR\Silencer\Exception\RestoreException;

/**
 * Implementation for entering and exiting silenced error environment.
 *
 * This is based heavily on Composer's Silencer utility class, which can be found at
 * https://github.com/composer/composer/blob/master/src/Composer/Util/Silencer.php
 */
class Silencer implements SilencerInterface
{
    /**
     * @var int[]
     */
    private static $priorLevelStack = [];

    /**
     * Enter silenced state.
     *
     * @param int $mask The new error mask to apply
     *
     * @return int
     */
    public static function silence($mask = self::DEFAULT_MASK)
    {
        $priorLevel = error_reporting();

        array_push(static::$priorLevelStack, $priorLevel);
        error_reporting($priorLevel & ~$mask);

        return $priorLevel;
    }

    /**
     * Exit silenced state.
     *
     * @throws RestoreException If {@see silence()} is not called prior
     *
     * @return int
     */
    public static function restore()
    {
        if (!static::hasPriorReportingLevels()) {
            throw new RestoreException('Cannot restore to an unknown prior error state.');
        }

        $restoreLevel = array_pop(static::$priorLevelStack);
        error_reporting($restoreLevel);

        return $restoreLevel;
    }

    /**
     * Returns true if silenced to default mask.
     *
     * @return bool|int
     */
    public static function isSilenced()
    {
        if (!static::hasPriorReportingLevels()) {
            return false;
        }

        $priorLevel = static::$priorLevelStack[count(static::$priorLevelStack) - 1];

        return (bool) ($priorLevel & ~self::DEFAULT_MASK === error_reporting() & ~self::DEFAULT_MASK);
    }

    /**
     * Returns true if prior restore state exists.
     *
     * @return bool
     */
    public static function hasPriorReportingLevels()
    {
        return count(static::$priorLevelStack) > 0;
    }
}

/* EOF */
