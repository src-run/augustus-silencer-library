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

use SR\Silencer\Exception\RestoreException;
use SR\Silencer\Silencer;

/**
 * @covers \SR\Silencer\Silencer
 */
class SilencerTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionOnRestoreBeforeSilence()
    {
        $this->expectException(RestoreException::class);
        $this->expectExceptionMessage('Cannot restore to an unknown prior error state.');

        Silencer::restore();
    }

    public function testDefaultSilenceAndRestore()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorReportingLevels());

        $priorLevel = Silencer::silence();

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('priorLevelStack');
        $rp->setAccessible(true);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorReportingLevels());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~Silencer::DEFAULT_MASK);

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::hasPriorReportingLevels());
        $this->assertFalse(Silencer::isSilenced());
        $this->assertSame(error_reporting(), $restoredLevel);
    }

    /**
     * @dataProvider silenceAndRestoreProvider
     *
     * @param int $mask
     */
    public function testSilenceAndRestore($mask)
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorReportingLevels());

        $priorLevel = Silencer::silence($mask);

        $rc = new \ReflectionClass(Silencer::class);
        $rp = $rc->getProperty('priorLevelStack');
        $rp->setAccessible(true);

        $this->assertFalse(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorReportingLevels());
        $this->assertCount(1, $rp->getValue());
        $this->assertSame(error_reporting(), $priorLevel & ~$mask);

        $restoredLevel = Silencer::restore();
        $this->assertFalse(Silencer::hasPriorReportingLevels());
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
