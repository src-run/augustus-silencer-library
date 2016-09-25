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
 * Interface for engine error reporting silencing/restoring API.
 */
interface SilencerInterface
{
    /**
     * The default silence mask to apply.
     */
    const NEGATIVE_SILENCE_MASK = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED | E_STRICT;

    /**
     * Enter new silenced error reporting level with optionally custom level mask.
     *
     * @param int|null $mask The new error mask to apply
     *
     * @return int
     */
    public static function silence(int $mask = null) : int;

    /**
     * Enter silenced error reporting level if not already in silenced state.
     *
     * @param int|null $mask The new error mask to apply
     *
     * @return int
     */
    public static function silenceIfNot(int $mask = null) : int;

    /**
     * Restore previous error reporting level assigned through call to {@see Silencer::silence}.
     *
     * @return int
     */
    public static function restore() : int;

    /**
     * Returns true if if current error reporting level equals the default silenced level.
     *
     * @return bool
     */
    public static function isSilenced() : bool;

    /**
     * Returns true if state is "restorable" (meaning a prior restore state exists).
     *
     * @return bool
     */
    public static function isRestorable() : bool;
}

/* EOF */
