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
use SR\Silencer\Call\CallDefinition;
use SR\Silencer\Call\Result\ResultInspector;
use SR\Silencer\CallSilencerFactory;
use SR\Silencer\Silencer;

/**
 * @covers \SR\Silencer\CallSilencerFactory
 * @covers \SR\Silencer\Call\CallDefinition
 * @covers \SR\Silencer\Call\Result\ResultInspector
 * @covers \SR\Silencer\Call\Runner\ClosureRunner
 */
class CallSilencerTest extends TestCase
{
    public function testStaticConstruct()
    {
        $this->assertInstanceOf(CallDefinition::class, CallSilencerFactory::create());
    }

    public function testStaticConstructAndInvoke()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create(function () {
            return true;
        });
        $ret = $silencer->invoke();

        $this->assertSame($ret, $silencer->getResult());
        $this->assertTrue($ret->getReturn());
        $this->assertTrue($ret->isTrue());
        $this->assertTrue($ret->hasReturn());
        $this->assertFalse($ret->isFalse());
        $this->assertFalse($ret->hasError());
        $this->assertTrue($ret->isValid());
        $this->assertEmpty($ret->getErrorMessage());
        $this->assertNull($ret->getErrorType());
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(CallDefinition::class, \SR\Silencer\CallSilencerFactory::create());
    }

    public function testConstructAndInvoke()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create(function () {
            return true;
        });
        $ret = $silencer->invoke();

        $this->assertTrue($ret->getReturn());
        $this->assertTrue($ret->isTrue());
        $this->assertFalse($ret->isFalse());
        $this->assertFalse($ret->hasError());
        $this->assertEmpty($ret->getErrorMessage());
        $this->assertNull($ret->getErrorType());
    }

    public function testInvokeWithRaisedError()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create();
        $ret = $silencer->setInvokable(function () {
            return file_put_contents('/tmp/does/not/exist.ext', '');
        })->invoke();

        $this->assertFalse($ret->getReturn());
        $this->assertFalse($ret->isTrue());
        $this->assertTrue($ret->isFalse());
        $this->assertTrue($ret->hasError());
        $this->assertMatchesRegularExpression('{(file_put_contents.+?failed to open stream.+?|Failed to open stream: No such file or directory)}', $ret->getErrorMessage());
        $this->assertNotNull($ret->getErrorType());
    }

    public function testPriorErrorsClearedOnInvokeWithRaisedError()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create();
        $ret = $silencer->setInvokable(function () {
            return file_put_contents('/tmp/does/not/exist.ext', '');
        })->invoke();

        $this->assertFalse($ret->isTrue());
        $this->assertTrue($ret->isFalse());
        $this->assertTrue($ret->hasError());
        $this->assertMatchesRegularExpression('{(file_put_contents.+?failed to open stream.+?|Failed to open stream: No such file or directory)}', $ret->getErrorMessage());
    }

    public function testResultValidator()
    {
        $silencer = CallSilencerFactory::create();
        $silencer->setInvokable(function () {
            return false;
        });
        $silencer->setValidator(function ($return) {
            return false === $return;
        });
        $ret = $silencer->invoke();

        $this->assertTrue($ret->isValid());
        $this->assertFalse($ret->isTrue());
        $this->assertTrue($ret->isFalse());
        $this->assertFalse($ret->hasError());
        $this->assertEmpty($ret->getErrorMessage());
        $this->assertNull($ret->getErrorType());
    }

    public function testStaticCreateWithClosureAndValidator()
    {
        $ret = CallSilencerFactory::create(function () {
            return false;
        }, function ($return) {
            return false === $return;
        })->invoke();

        $this->assertTrue($ret->isValid());
        $this->assertFalse($ret->isTrue());
        $this->assertTrue($ret->isFalse());
        $this->assertFalse($ret->hasError());
        $this->assertEmpty($ret->getErrorMessage());
        $this->assertNull($ret->getErrorType());
        $this->assertTrue($ret->isCalled());
    }

    public function testStaticCreateWithClosureAndThrowingValidator()
    {
        $ret = CallSilencerFactory::create(function () {
        }, function () {
            throw new \Exception('An error occured!');
        })->invoke();

        $this->assertTrue($ret->isCalled());
        $this->assertFalse($ret->isValid());
        $this->assertFalse($ret->isTrue());
        $this->assertFalse($ret->isFalse());
        $this->assertFalse($ret->hasError());
        $this->assertEmpty($ret->getErrorMessage());
        $this->assertNull($ret->getErrorType());
    }

    public function testDoesNothingOnUnsetClosure()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create();
        $ret = $silencer->invoke();

        $this->assertFalse($ret->isCalled());
    }

    public function testInnerClosureExceptionIsReThrown()
    {
        $silencer = \SR\Silencer\CallSilencerFactory::create();
        $silencer->setInvokable(function () {
            throw new \Exception('Inner closure exception.');
        });

        $this->expectException(\RuntimeException::class);

        $ret = $silencer->invoke();
    }

    public function testCallsSilenceOnlyWhenRequired()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorState());

        $silencer = CallSilencerFactory::create();
        $silencer->setInvokable(function () {
            return true;
        });

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorState());

        Silencer::restore();
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorState());

        $silencer = \SR\Silencer\CallSilencerFactory::create();
        $silencer->setInvokable(function () {
            return true;
        });
        $silencer->invoke(true);

        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorState());
    }

    public function testInvokableBind()
    {
        $silencer = CallSilencerFactory::create();
        $ret = $silencer->setInvokable(function () {
            return static::class;
        }, $silencer)->invoke();

        $this->assertTrue($ret->isCalled());
        $this->assertInstanceOf(ResultInspector::class, $silencer->getResult());
    }
}
