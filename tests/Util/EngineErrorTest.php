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

use SR\Silencer\Util\EngineError;

/**
 * @covers \SR\Silencer\Util\EngineError
 */
class EngineErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $this->assertFalse(EngineError::hasLastError());
        $this->assertNull(EngineError::getLastError());

        @file_put_contents('/tmp/file/does/not/exist.out', null);

        $this->assertTrue(EngineError::hasLastError());
        $this->assertNotNull(EngineError::getLastError());

        EngineError::clearLastError();

        $this->assertFalse(EngineError::hasLastError());
        $this->assertNull(EngineError::getLastError());
    }
}

/* EOF */
