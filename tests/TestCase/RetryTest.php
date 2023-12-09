<?php

namespace Basko\FunctionalTest\TestCase;

use Basko\Functional as f;

interface Retryer
{
    public function retry();
}

class RetryTest extends BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit\Framework\MockObject\MockObject
     */
    private $retryer;

    /**
     * @before
     */
    protected function setUpRetryer()
    {
        $this->retryer = $this->mock(Retryer::class);
    }

    /**
     * @param string $message
     * @return void
     */
    protected function expectArgumentError($message)
    {
        $this->setExpectedException(\InvalidArgumentException::class, $message);
    }

    public function test_retry_fail()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'retry() expects parameter 3 to be a valid callback, array, string, closure or functor, NULL given'
        );
        f\retry(2, f\no_delay(), null);
    }

    public function testTriedOnceIfItSucceeds()
    {
        $this->retryer
            ->expects(self::once())
            ->method('retry')
            ->with(0, 0)
            ->willReturn('value');

        $retry10 = f\retry(10);
        $retry10WithoutDelay = $retry10(f\no_delay());
        $this->assertSame('value', $retry10WithoutDelay([$this->retryer, 'retry']));
    }

    public function testRetriedIfItFails()
    {
        $this->retryer
            ->expects(self::exactly(2))
            ->method('retry')
            ->withConsecutive([0, 0], [1, 0])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception()),
                'value'
            );

        $this->assertSame('value', f\retry(10, f\no_delay(), [$this->retryer, 'retry']));
    }

    public function testThrowsExceptionIfRetryCountIsReached()
    {
        $this->retryer
            ->method('retry')
            ->withConsecutive([0, 0], [1, 0])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second'))
            );


        $this->setExpectedException(\Exception::class, 'second');

        f\retry(2, f\no_delay(), [$this->retryer, 'retry']);
    }

    public function testRetryWithEmptyDelaySequence()
    {
        $this->retryer
            ->method('retry')
            ->withConsecutive([0, 0], [1, 0])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second'))
            );

        $this->setExpectedException(\Exception::class, 'second');

        f\retry(2, new \ArrayIterator([]), [$this->retryer, 'retry']);
    }

    public function testThrowsExceptionIfRetryCountNotAtLeast1()
    {
        $this->expectArgumentError(
            'Basko\Functional\retry() expects parameter 1 to be an integer greater than or equal to 1'
        );
        f\retry(0, f\no_delay(), [$this->retryer, 'retry']);
    }

    public function testUsesDelayTraversableForSleeping()
    {
        $this->retryer
            ->method('retry')
            ->withConsecutive([0, 0], [1, 0])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second'))
            );

        $this->setExpectedException(\Exception::class, 'second');

        f\retry(2, f\no_delay(), [$this->retryer, 'retry']);
    }

    public function testDelayerSmallerThanRetries()
    {
        $this->retryer
            ->method('retry')
            ->withConsecutive([0, 10], [1, 20], [2, 30], [3, 10])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second')),
                self::throwException(new \Exception('third')),
                self::throwException(new \Exception('fourth'))
            );

        $this->setExpectedException(\Exception::class, 'fourth');

        f\retry(4, new \ArrayIterator([10, 20, 30]), [$this->retryer, 'retry']);
    }

    public function testRetry5Linear()
    {
        $this->retryer
            ->expects(self::exactly(5))
            ->method('retry')
            ->withConsecutive([0, 1], [1, 6], [2, 11], [3, 16], [4, 21])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second')),
                self::throwException(new \Exception('third')),
                self::throwException(new \Exception('fourth')),
                self::throwException(new \Exception('fifth'))
            );

        $this->setExpectedException(\Exception::class, 'fifth');

        f\retry(5, f\sequence_linear(1, 5), [$this->retryer, 'retry']);
    }

    public function testRetry10()
    {
        $this->retryer
            ->expects(self::exactly(10))
            ->method('retry')
            ->withConsecutive([0, 100000], [1, 100000], [2, 100000], [3, 100000], [4, 100000], [5, 100000], [6, 100000], [7, 100000], [8, 100000], [9, 100000])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second')),
                self::throwException(new \Exception('third')),
                self::throwException(new \Exception('fourth')),
                self::throwException(new \Exception('fifth')),
                self::throwException(new \Exception('sixth')),
                self::throwException(new \Exception('seventh')),
                self::throwException(new \Exception('eighth')),
                self::throwException(new \Exception('ninth')),
                'tenth'
            );

        $start = microtime(true);
        $this->assertSame(
            'tenth',
            f\retry(10, f\sequence_constant(100000), [$this->retryer, 'retry'])
        );
        $end = microtime(true);

        $this->assertGreaterThanOrEqual(0.9, $end - $start);
    }

    public function testRetry2Each1Sec()
    {
        $this->retryer
            ->expects(self::exactly(3))
            ->method('retry')
            ->withConsecutive([0, 1000000], [1, 2000000], [2, 4000000000000])
            ->willReturnOnConsecutiveCalls(
                self::throwException(new \Exception('first')),
                self::throwException(new \Exception('second')),
                'third'
            );

        $start = microtime(true);
        $this->assertSame(
            'third',
            f\retry(3, f\sequence_exponential(1000000, 100), [$this->retryer, 'retry'])
        );
        $end = microtime(true);

        $this->assertGreaterThanOrEqual(2.9, $end - $start);
    }
}