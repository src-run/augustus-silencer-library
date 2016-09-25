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

use SR\Silencer\CallSilencer;
use SR\Silencer\Silencer;

/**
 * @covers \SR\Silencer\CallSilencer
 */
class CallSilencerTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticConstruct()
    {
        $this->assertInstanceOf(CallSilencer::class, CallSilencer::create());
    }

    public function testStaticConstructAndInvoke()
    {
        $silencer = CallSilencer::create(function () {
            return true;
        })->invoke();

        $this->assertTrue($silencer->hasResult());
        $this->assertTrue($silencer->getResult());
        $this->assertTrue($silencer->isResultTrue());
        $this->assertFalse($silencer->isResultFalse());
        $this->assertFalse($silencer->hasError());
        $this->assertTrue($silencer->isResultValid());
        $this->assertEmpty($silencer->getErrorMessage());
        $this->assertSame(0, $silencer->getErrorType());
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(CallSilencer::class, new CallSilencer());
    }

    public function testConstructAndInvoke()
    {
        $silencer = new CallSilencer(function () {
            return true;
        });
        $silencer->invoke();

        $this->assertTrue($silencer->hasResult());
        $this->assertTrue($silencer->getResult());
        $this->assertTrue($silencer->isResultTrue());
        $this->assertFalse($silencer->isResultFalse());
        $this->assertFalse($silencer->hasError());
        $this->assertEmpty($silencer->getErrorMessage());
        $this->assertSame(0, $silencer->getErrorType());
    }

    public function testInvokeWithRaisedError()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return file_put_contents('/tmp/does/not/exist.ext', '');
        })->invoke();

        $this->assertTrue($silencer->hasResult());
        $this->assertFalse($silencer->getResult());
        $this->assertFalse($silencer->isResultTrue());
        $this->assertTrue($silencer->isResultFalse());
        $this->assertTrue($silencer->hasError());
        $this->assertRegExp('{file_put_contents.+?failed to open stream.+?}', $silencer->getErrorMessage());
        $this->assertNotSame(0, $silencer->getErrorType());
    }

    public function testPriorErrorsClearedOnInvokeWithRaisedError()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return file_put_contents('/tmp/does/not/exist.ext', '');
        })->invoke();

        $this->assertTrue($silencer->hasResult());
        $this->assertFalse($silencer->getResult());
        $this->assertFalse($silencer->isResultTrue());
        $this->assertTrue($silencer->isResultFalse());
        $this->assertTrue($silencer->hasError());
        $this->assertRegExp('{file_put_contents.+?failed to open stream.+?}', $silencer->getErrorMessage());
        $this->assertNotSame(0, $silencer->getErrorType());
    }

    public function testResultValidator()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return false;
        });
        $silencer->setValidator(function ($return) {
            return $return === false;
        });
        $silencer->invoke();

        $this->assertTrue($silencer->isResultValid());
        $this->assertTrue($silencer->hasResult());
        $this->assertFalse($silencer->getResult());
        $this->assertTrue($silencer->isResultFalse());
        $this->assertFalse($silencer->isResultTrue());
        $this->assertFalse($silencer->hasError());
        $this->assertEmpty($silencer->getErrorMessage());
        $this->assertSame(0, $silencer->getErrorType());
    }

    public function testStaticCreateWithClosureAndValidator()
    {
        $silencer = CallSilencer::create(function () {
            return false;
        }, function ($return) {
            return $return === false;
        })->invoke();

        $this->assertTrue($silencer->isResultValid());
        $this->assertTrue($silencer->hasResult());
        $this->assertFalse($silencer->getResult());
        $this->assertTrue($silencer->isResultFalse());
        $this->assertFalse($silencer->isResultTrue());
        $this->assertFalse($silencer->hasError());
        $this->assertEmpty($silencer->getErrorMessage());
        $this->assertSame(0, $silencer->getErrorType());
    }

    public function testDoesNothingOnUnsetClosure()
    {
        $silencer = new CallSilencer();
        $silencer->invoke();

        $this->assertFalse($silencer->isInvoked());
    }

    public function testInnerClosureExceptionIsReThrown()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            throw new \Exception('Inner closure exception.');
        });

        $this->expectException(\Exception::class);

        $silencer->invoke();
    }

    public function testCallsSilenceOnlyWhenRequired()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::isRestorable());

        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke(false);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::isRestorable());

        Silencer::restore();
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::isRestorable());

        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke(true);

        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::isRestorable());
    }
}

/* EOF */
