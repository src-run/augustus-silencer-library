<?php

/*
 * This file is part of the `src-run/augustus-silencer-library` project.
 *
 * (c) Rob Frawley 2nd <rmf@src.run>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace SR\Silencer\Tests\Call\Engine;

use SR\Silencer\Call\Engine\EngineError;

/**
 * @covers \SR\Silencer\Call\Engine\EngineError
 */
class EngineErrorTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $this->assertFalse(EngineError::hasLast());
        $this->assertNull(EngineError::getLast());

        @file_put_contents('/tmp/file/does/not/exist.out', null);

        $this->assertTrue(EngineError::hasLast());
        $this->assertNotNull(EngineError::getLast());

        EngineError::clearLast();

        $this->assertFalse(EngineError::hasLast());
        $this->assertNull(EngineError::getLast());
    }
}

/* EOF */
