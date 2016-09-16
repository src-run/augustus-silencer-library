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
use SR\Silencer\Exception\InvocationException;
use SR\Silencer\Exception\ResultValidatorException;
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
        var_dump($silencer->getError());
        $this->assertFalse($silencer->hasError());
        $this->assertFalse($silencer->getError());
        $this->assertFalse($silencer->getError(CallSilencer::ERROR_MESSAGE));
        $this->assertFalse($silencer->getError('invalid-index'));
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(CallSilencer::class, new CallSilencer());
    }

    public function testConstructAndInvoke()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke();

        $this->assertTrue($silencer->hasResult());
        $this->assertTrue($silencer->getResult());
        $this->assertTrue($silencer->isResultTrue());
        $this->assertFalse($silencer->isResultFalse());
        $this->assertFalse($silencer->hasError());
        $this->assertFalse($silencer->getError());
        $this->assertFalse($silencer->getError(CallSilencer::ERROR_MESSAGE));
        $this->assertFalse($silencer->getError('invalid-index'));
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
        $this->assertRegExp('{file_put_contents.+?failed to open stream.+?}', $silencer->getError(CallSilencer::ERROR_MESSAGE));
        $this->assertArrayHasKey('line', $silencer->getError());
        $this->assertFalse($silencer->getError('invalid-index'));
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
        $this->assertRegExp('{file_put_contents.+?failed to open stream.+?}', $silencer->getError(CallSilencer::ERROR_MESSAGE));
        $this->assertArrayHasKey('line', $silencer->getError());
        $this->assertFalse($silencer->getError('invalid-index'));
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
        $this->assertFalse($silencer->getError());
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
        $this->assertFalse($silencer->getError());
    }

    public function testThrowsExceptionOnUnsetClosure()
    {
        $silencer = new CallSilencer();

        $this->expectException(InvocationException::class);
        $this->expectExceptionMessage('Cannot call undefined. Set closure before calling invoke.');

        $silencer->invoke();
    }

    public function testThrowsExceptionOnUnsetValidatorClosure()
    {
        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke();

        $this->expectException(ResultValidatorException::class);
        $this->expectExceptionMessage('Unable to validate result if validator closure is unset.');

        $this->assertTrue($silencer->isResultValid());
    }

    public function testThrowsExceptionOnClosureException()
    {
        $exception = new \Exception('Inner closure exception.');

        try {
            $silencer = new CallSilencer();
            $silencer->setClosure(function () use ($exception) {
                throw $exception;
            });
            $silencer->invoke();
        } catch (InvocationException $e) {
            $this->assertSame('Exception thrown while silently invoking closure.', $e->getMessage());
            $this->assertSame($exception, $e->getPrevious());

            return;
        }

        $this->fail('Expected exception '.InvocationException::class.' was not thrown.');
    }

    public function testCallsSilenceOnlyWhenRequired()
    {
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorReportingLevels());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorReportingLevels());

        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke(false);

        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorReportingLevels());

        Silencer::restore();
        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorReportingLevels());

        Silencer::silence();
        $this->assertTrue(Silencer::isSilenced());
        $this->assertTrue(Silencer::hasPriorReportingLevels());

        $silencer = new CallSilencer();
        $silencer->setClosure(function () {
            return true;
        });
        $silencer->invoke(true);

        $this->assertFalse(Silencer::isSilenced());
        $this->assertFalse(Silencer::hasPriorReportingLevels());
    }
}

/* EOF */
