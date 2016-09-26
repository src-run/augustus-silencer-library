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

use SR\Silencer\Util\PhpError;

/**
 * @covers \SR\Silencer\Util\PhpError
 */
class EngineErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $this->assertFalse(PhpError::hasLastError());
        $this->assertNull(PhpError::getLastError());

        @file_put_contents('/tmp/file/does/not/exist.out', null);

        $this->assertTrue(PhpError::hasLastError());
        $this->assertNotNull(PhpError::getLastError());

        PhpError::clearLastError();

        $this->assertFalse(PhpError::hasLastError());
        $this->assertNull(PhpError::getLastError());
    }
}

/* EOF */
