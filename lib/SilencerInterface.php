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
 * Interface for entering and exiting silenced error environment.
 */
interface SilencerInterface
{
    /**
     * The default silence mask to apply.
     */
    const DEFAULT_MASK = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED | E_STRICT;

    /**
     * Enter silenced state.
     *
     * @param int $mask Error reporting level mask to apply
     *
     * @return int
     */
    public static function silence($mask = null);

    /**
     * Exit silenced state.
     *
     * @return int
     */
    public static function restore();

    /**
     * Returns true if current state is silenced.
     *
     * @return bool|int
     */
    public static function isSilenced();

    /**
     * Returns true if prior restore state exists.
     *
     * @return bool
     */
    public static function hasPriorReportingLevels();
}

/* EOF */
