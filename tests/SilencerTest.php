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

use SR\Silencer\Silencer;

/**
 * @covers \SR\Silencer\Silencer
 */
class SilencerTest extends \PHPUnit_Framework_TestCase
{
    public function testSilenceAndRestore()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());

        $priorLevel = Silencer::silence();

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('reportingLevelHistory');
        $rp->setAccessible(true);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::isRestorable());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~Silencer::NEGATIVE_SILENCE_MASK);

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::isRestorable());
        $this->assertFalse(Silencer::isSilenced());
        $this->assertSame(error_reporting(), $restoredLevel);
    }

    public function testRestoreWhenNotRestorable()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());

        $this->assertSame(error_reporting(), Silencer::restore());
    }

    /**
     * @dataProvider silenceAndRestoreProvider
     */
    public function testSilenceAndRestoreWithDifferentErrorMasks($mask)
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());

        $priorLevel = Silencer::silence($mask);

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('reportingLevelHistory');
        $rp->setAccessible(true);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::isRestorable());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~$mask);

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::isRestorable());
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

/* EOF */
