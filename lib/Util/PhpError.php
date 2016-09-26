<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Util;

/**
 * Simple engine error API.
 */
final class PhpError
{
    /**
     * Clears the last engine error.
     */
    public static function clearLastError()
    {
        return error_clear_last();
    }

    /**
     * Returns true if a previous error exists.
     *
     * @return bool
     */
    public static function hasLastError() : bool
    {
        return static::isErrorValid(error_get_last());
    }

    /**
     * Returns the last engine error, if one exists.
     *
     * @return mixed[]|null
     */
    public static function getLastError()
    {
        return static::hasLastError() ? error_get_last() : null;
    }

    /**
     * @param mixed[]|null $error
     *
     * @return bool
     */
    private static function isErrorValid(array $error = null) : bool
    {
        foreach (['message', 'type', 'line', 'file'] as $index) {
            if (!isset($error[$index]) || empty($error[$index])) {
                return false;
            }
        }

        return true;
    }
}

/* EOF */
