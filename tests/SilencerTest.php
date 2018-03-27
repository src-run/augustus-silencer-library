<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Tests;

use PHPUnit\Framework\TestCase;
use SR\Silencer\Silencer;

/**
 * @covers \SR\Silencer\Silencer
 */
class SilencerTest extends TestCase
{
    public function testSilenceAndRestore()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());

        $priorLevel = Silencer::silence();

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('reportingLevelHistory');
        $rp->setAccessible(true);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorState());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~(E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED | E_STRICT));

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::hasPriorState());
        $this->assertFalse(Silencer::isSilenced());
        $this->assertSame(error_reporting(), $restoredLevel);
    }

    public function testRestoreWhenNotRestorable()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());

        $this->assertSame(error_reporting(), Silencer::restore());
    }

    /**
     * @dataProvider silenceAndRestoreProvider
     */
    public function testSilenceAndRestoreWithDifferentErrorMasks($mask)
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());

        $priorLevel = Silencer::silenceIfNot($mask);

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('reportingLevelHistory');
        $rp->setAccessible(true);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorState());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~$mask);

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::hasPriorState());
        $this->assertFalse(Silencer::isSilenced());
        $this->assertSame(error_reporting(), $restoredLevel);
    }

    public function silenceAndRestoreProvider()
    {
        return [
            [E_WARNING],
            [E_NOTICE],
            [E_USER_WARNING],
            [E_USER_NOTICE],
            [E_DEPRECATED],
            [E_USER_DEPRECATED],
            [E_STRICT],
        ];
    }
}
