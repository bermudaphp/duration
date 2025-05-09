<?php

namespace Bermuda\Stdlib\Tests;

use Bermuda\Stdlib\Duration;
use PHPUnit\Framework\TestCase;

class DurationTest extends TestCase
{
    /**
     * Test basic construction and getters
     */
    public function testBasicConstruction(): void
    {
        $duration = new Duration(3661); // 1h 1m 1s
        
        $this->assertEquals(3661, $duration->toSeconds());
        $this->assertEquals(61, $duration->toMinutes());
        $this->assertEquals(1, $duration->toHours());
        $this->assertEquals(0, $duration->toDays());
    }
    
    /**
     * Test negative values are handled correctly (set to 0)
     */
    public function testNegativeValues(): void
    {
        $duration = new Duration(-100);
        $this->assertEquals(0, $duration->toSeconds());
    }
    
    /**
     * Test the factory methods
     */
    public function testFactoryMethods(): void
    {
        $fromSeconds = Duration::fromSeconds(60);
        $this->assertEquals(60, $fromSeconds->toSeconds());
        
        $fromMinutes = Duration::fromMinutes(2);
        $this->assertEquals(120, $fromMinutes->toSeconds());
        
        $fromHours = Duration::fromHours(1);
        $this->assertEquals(3600, $fromHours->toSeconds());
        
        $fromDays = Duration::fromDays(1);
        $this->assertEquals(86400, $fromDays->toSeconds());
        
        $fromWeeks = Duration::fromWeeks(1);
        $this->assertEquals(604800, $fromWeeks->toSeconds());
        
        $fromMonths = Duration::fromMonths(1);
        $this->assertEquals(2629746, $fromMonths->toSeconds());
        
        $fromYears = Duration::fromYears(1);
        $this->assertEquals(31557600, $fromYears->toSeconds());
    }
    
    /**
     * Test ISO 8601 duration format validation
     */
    public function testIso8601Validation(): void
    {
        // Valid formats
        $this->assertTrue(Duration::validate('P1Y'));
        $this->assertTrue(Duration::validate('P1M'));
        $this->assertTrue(Duration::validate('P1D'));
        $this->assertTrue(Duration::validate('PT1H'));
        $this->assertTrue(Duration::validate('PT1M'));
        $this->assertTrue(Duration::validate('PT1S'));
        $this->assertTrue(Duration::validate('P1Y2M3DT4H5M6S'));
        $this->assertTrue(Duration::validate('PT0S')); // Zero duration
        
        // Invalid formats
        $this->assertFalse(Duration::validate('P'));
        $this->assertFalse(Duration::validate('PT'));
        $this->assertFalse(Duration::validate('1Y2M'));
        $this->assertFalse(Duration::validate('P1Y2MT'));
        $this->assertFalse(Duration::validate('PT1H2'));
    }
    
    /**
     * Test creation from ISO 8601 string
     */
    public function testFromIso8601(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        
        // Expected seconds calculation:
        // 1 year = 31557600 seconds
        // 2 months = 2 * 2629746 = 5259492 seconds
        // 3 days = 3 * 86400 = 259200 seconds
        // 4 hours = 4 * 3600 = 14400 seconds
        // 5 minutes = 5 * 60 = 300 seconds
        // 6 seconds = 6 seconds
        // Total: 31557600 + 5259492 + 259200 + 14400 + 300 + 6 = 37090998 seconds
        $this->assertEquals(37090998, $duration->toSeconds());
        
        // Test zero duration
        $zeroDuration = Duration::fromISO8601('PT0S');
        $this->assertEquals(0, $zeroDuration->toSeconds());
    }
    
    /**
     * Test exception for invalid ISO 8601 format
     */
    public function testFromIso8601WithInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Duration::fromISO8601('invalid');
    }
    
    /**
     * Test conversion from DateInterval
     */
    public function testFromDateInterval(): void
    {
        $interval = new \DateInterval('P1Y2M3DT4H5M6S');
        $duration = Duration::fromDateInterval($interval);
        
        // Using the same calculation as in testFromIso8601
        $this->assertEquals(37090998, $duration->toSeconds());
    }
    
    /**
     * Test conversion methods (toX methods)
     */
    public function testConversionMethods(): void
    {
        $duration = new Duration(90061); // 1 day, 1 hour, 1 minute, 1 second
        
        $this->assertEquals(90061, $duration->toSeconds());
        $this->assertEquals(1501, $duration->toMinutes());
        $this->assertEquals(25, $duration->toHours());
        $this->assertEquals(1, $duration->toDays());
        $this->assertEquals(0, $duration->toWeeks());
        $this->assertEquals(0, $duration->toMonths());
        $this->assertEquals(0, $duration->toYears());
    }
    
    /**
     * Test add methods
     */
    public function testAddMethods(): void
    {
        $duration = new Duration(0);
        
        $this->assertEquals(31557600, $duration->addYears(1)->toSeconds());
        $this->assertEquals(2629746, $duration->addMonths(1)->toSeconds());
        $this->assertEquals(604800, $duration->addWeeks(1)->toSeconds());
        $this->assertEquals(86400, $duration->addDays(1)->toSeconds());
        $this->assertEquals(3600, $duration->addHours(1)->toSeconds());
        $this->assertEquals(60, $duration->addMinutes(1)->toSeconds());
        $this->assertEquals(1, $duration->addSeconds(1)->toSeconds());
        
        // Test chaining
        $result = $duration->addDays(1)->addHours(2)->addMinutes(3)->addSeconds(4);
        $this->assertEquals(86400 + 7200 + 180 + 4, $result->toSeconds());
    }
    
    /**
     * Test subtract methods
     */
    public function testSubtractMethods(): void
    {
        $duration = new Duration(100000);
        
        $this->assertEquals(100000 - 60, $duration->subtractMinutes(1)->toSeconds());
        $this->assertEquals(100000 - 3600, $duration->subtractHours(1)->toSeconds());
        
        // Test exception when result would be negative
        $this->expectException(\InvalidArgumentException::class);
        $duration->subtractSeconds(200000);
    }
    
    /**
     * Test toArray method
     */
    public function testToArray(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $array = $duration->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('years', $array);
        $this->assertArrayHasKey('months', $array);
        $this->assertArrayHasKey('days', $array);
        $this->assertArrayHasKey('hours', $array);
        $this->assertArrayHasKey('minutes', $array);
        $this->assertArrayHasKey('seconds', $array);
        
        $this->assertEquals(1, $array['years']);
        $this->assertEquals(2, $array['months']);
        $this->assertEquals(3, $array['days']);
        $this->assertEquals(4, $array['hours']);
        $this->assertEquals(5, $array['minutes']);
        $this->assertEquals(6, $array['seconds']);
        
        // Test zero duration
        $zeroDuration = new Duration(0);
        $zeroArray = $zeroDuration->toArray();
        $this->assertCount(1, $zeroArray);
        $this->assertArrayHasKey('seconds', $zeroArray);
        $this->assertEquals(0, $zeroArray['seconds']);
    }
    
    /**
     * Test toHumanReadable method
     */
    public function testToHumanReadable(): void
    {
        // Test formats based on duration length
        $this->assertEquals('00:05', (new Duration(5))->toHumanReadable()); // Seconds only
        $this->assertEquals('01:30', (new Duration(90))->toHumanReadable()); // Minutes and seconds
        $this->assertEquals('02:00:00', (new Duration(7200))->toHumanReadable()); // Hours, minutes, seconds
        $this->assertEquals('01:00:00:00', (new Duration(86400))->toHumanReadable()); // Days, hours, minutes, seconds
        $this->assertEquals('01:00:00:00:00', (new Duration(2629746))->toHumanReadable()); // Months, days, hours, minutes, seconds
        $this->assertEquals('01:00:00:00:00:00', (new Duration(31557600))->toHumanReadable()); // Years, months, days, hours, minutes, seconds
        
        // Complex example
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $this->assertEquals('01:02:03:04:05:06', $duration->toHumanReadable());
    }
    
    /**
     * Test add, subtract, multiply, divide operations
     */
    public function testArithmeticOperations(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        
        // Add
        $added = $duration1->add($duration2);
        $this->assertEquals(150, $added->toSeconds());
        
        // Subtract
        $subtracted = $duration1->subtract($duration2);
        $this->assertEquals(50, $subtracted->toSeconds());
        
        // Strict subtract
        $strictSubtracted = $duration1->strictSubtract($duration2);
        $this->assertEquals(50, $strictSubtracted->toSeconds());
        
        // Multiply
        $multiplied = $duration1->multiply(2);
        $this->assertEquals(200, $multiplied->toSeconds());
        
        // Divide
        $divided = $duration1->divide(2);
        $this->assertEquals(50, $divided->toSeconds());
        
        // Test exception when dividing by zero
        $this->expectException(\InvalidArgumentException::class);
        $duration1->divide(0);
    }
    
    /**
     * Test compareTo method
     */
    public function testCompareTo(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        $duration3 = new Duration(100);
        
        $this->assertGreaterThan(0, $duration1->compareTo($duration2)); // $duration1 > $duration2
        $this->assertLessThan(0, $duration2->compareTo($duration1)); // $duration2 < $duration1
        $this->assertEquals(0, $duration1->compareTo($duration3)); // $duration1 == $duration3
    }
    
    /**
     * Test equals method with single Duration
     */
    public function testEqualsSingleDuration(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(100);
        $duration3 = new Duration(50);
        
        $this->assertTrue($duration1->equals($duration2));
        $this->assertFalse($duration1->equals($duration3));
    }
    
    /**
     * Test equals method with array of Durations in ALL mode
     */
    public function testEqualsArrayAllMode(): void
    {
        $duration = new Duration(100);
        $array1 = [new Duration(100), new Duration(100), new Duration(100)];
        $array2 = [new Duration(100), new Duration(50), new Duration(100)];
        
        $this->assertTrue($duration->equals($array1, Duration::COMPARE_ALL));
        $this->assertFalse($duration->equals($array2, Duration::COMPARE_ALL));
    }
    
    /**
     * Test equals method with array of Durations in ANY mode
     */
    public function testEqualsArrayAnyMode(): void
    {
        $duration = new Duration(100);
        $array1 = [new Duration(50), new Duration(75), new Duration(100)];
        $array2 = [new Duration(50), new Duration(75), new Duration(200)];
        
        $this->assertTrue($duration->equals($array1, Duration::COMPARE_ANY));
        $this->assertFalse($duration->equals($array2, Duration::COMPARE_ANY));
    }
    
    /**
     * Test lessThan method with single Duration
     */
    public function testLessThanSingleDuration(): void
    {
        $duration1 = new Duration(50);
        $duration2 = new Duration(100);
        
        $this->assertTrue($duration1->lessThan($duration2));
        $this->assertFalse($duration2->lessThan($duration1));
    }
    
    /**
     * Test lessThan method with array of Durations in ALL mode
     */
    public function testLessThanArrayAllMode(): void
    {
        $duration = new Duration(50);
        $array1 = [new Duration(100), new Duration(200), new Duration(300)];
        $array2 = [new Duration(100), new Duration(25), new Duration(300)];
        
        $this->assertTrue($duration->lessThan($array1, Duration::COMPARE_ALL));
        $this->assertFalse($duration->lessThan($array2, Duration::COMPARE_ALL));
    }
    
    /**
     * Test lessThan method with array of Durations in ANY mode
     */
    public function testLessThanArrayAnyMode(): void
    {
        $duration = new Duration(50);
        $array1 = [new Duration(25), new Duration(100), new Duration(30)];
        $array2 = [new Duration(25), new Duration(30), new Duration(40)];
        
        $this->assertTrue($duration->lessThan($array1, Duration::COMPARE_ANY));
        $this->assertFalse($duration->lessThan($array2, Duration::COMPARE_ANY));
    }
    
    /**
     * Test lessThanOrEqual method (similar tests for other comparison methods)
     */
    public function testLessThanOrEqual(): void
    {
        $duration1 = new Duration(50);
        $duration2 = new Duration(50);
        $duration3 = new Duration(100);
        
        $this->assertTrue($duration1->lessThanOrEqual($duration2));
        $this->assertTrue($duration1->lessThanOrEqual($duration3));
        $this->assertFalse($duration3->lessThanOrEqual($duration1));
    }
    
    /**
     * Test greaterThan and greaterThanOrEqual methods
     */
    public function testGreaterThanAndGreaterThanOrEqual(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        $duration3 = new Duration(100);
        
        // greaterThan
        $this->assertTrue($duration1->greaterThan($duration2));
        $this->assertFalse($duration1->greaterThan($duration3));
        
        // greaterThanOrEqual
        $this->assertTrue($duration1->greaterThanOrEqual($duration2));
        $this->assertTrue($duration1->greaterThanOrEqual($duration3));
        $this->assertFalse($duration2->greaterThanOrEqual($duration1));
    }
    
    /**
     * Test between method
     */
    public function testBetween(): void
    {
        $duration1 = new Duration(50);
        $duration2 = new Duration(100);
        $duration3 = new Duration(150);
        
        // Test inclusive mode (default)
        $this->assertTrue($duration2->between($duration1, $duration3));
        $this->assertTrue($duration1->between($duration1, $duration3)); // Edge case, but included
        $this->assertTrue($duration3->between($duration1, $duration3)); // Edge case, but included
        
        // Test exclusive mode
        $this->assertTrue($duration2->between($duration1, $duration3, false));
        $this->assertFalse($duration1->between($duration1, $duration3, false));
        $this->assertFalse($duration3->between($duration1, $duration3, false));
    }
    
    /**
     * Test format method
     */
    public function testFormat(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        
        $this->assertEquals('01:02:03', $duration->format('%Y:%M:%D'));
        $this->assertEquals('04:05:06', $duration->format('%H:%I:%S'));
        $this->assertEquals('37090998', $duration->format('%T'));
        $this->assertEquals('Year: 01, Month: 02, Day: 03', $duration->format('Year: %Y, Month: %M, Day: %D'));
    }
    
    /**
     * Test toISO8601 method
     */
    public function testToIso8601(): void
    {
        $this->assertEquals('PT0S', (new Duration(0))->toISO8601()); // Zero duration
        $this->assertEquals('PT1S', (new Duration(1))->toISO8601()); // Only seconds
        $this->assertEquals('PT1M', (new Duration(60))->toISO8601()); // Only minutes
        $this->assertEquals('PT1H', (new Duration(3600))->toISO8601()); // Only hours
        $this->assertEquals('P1D', (new Duration(86400))->toISO8601()); // Only days
        
        $duration = new Duration(90061); // 1 day, 1 hour, 1 minute, 1 second
        $this->assertEquals('P1DT1H1M1S', $duration->toISO8601());
        
        $complexDuration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $this->assertEquals('P1Y2M3DT4H5M6S', $complexDuration->toISO8601());
    }
    
    /**
     * Test toDateInterval method
     */
    public function testToDateInterval(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $interval = $duration->toDateInterval();
        
        $this->assertInstanceOf(\DateInterval::class, $interval);
        $this->assertEquals(1, $interval->y);
        $this->assertEquals(2, $interval->m);
        $this->assertEquals(3, $interval->d);
        $this->assertEquals(4, $interval->h);
        $this->assertEquals(5, $interval->i);
        $this->assertEquals(6, $interval->s);
    }
    
    /**
     * Test __toString method
     */
    public function testToString(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $this->assertEquals('P1Y2M3DT4H5M6S', (string)$duration);
    }
    
    /**
     * Test jsonSerialize method
     */
    public function testJsonSerialize(): void
    {
        $duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');
        $json = json_encode($duration);
        $decoded = json_decode($json, true);
        
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('seconds', $decoded);
        $this->assertArrayHasKey('iso8601', $decoded);
        $this->assertArrayHasKey('components', $decoded);
        
        $this->assertEquals('P1Y2M3DT4H5M6S', $decoded['iso8601']);
        $this->assertEquals(37090998, $decoded['seconds']);
        
        $components = $decoded['components'];
        $this->assertEquals(1, $components['years']);
        $this->assertEquals(2, $components['months']);
        $this->assertEquals(3, $components['days']);
        $this->assertEquals(4, $components['hours']);
        $this->assertEquals(5, $components['minutes']);
        $this->assertEquals(6, $components['seconds']);
    }
    
    /**
     * Test increment method
     */
    public function testIncrement(): void
    {
        $duration = new Duration(100);
        $incremented = $duration->increment();
        
        $this->assertEquals(101, $incremented->toSeconds());
        $this->assertEquals(100, $duration->toSeconds()); // Original should be unchanged
    }
    
    /**
     * Test incrementBy method
     */
    public function testIncrementBy(): void
    {
        $duration = new Duration(100);
        
        $incrementedBy1 = $duration->incrementBy(); // Default is 1
        $this->assertEquals(101, $incrementedBy1->toSeconds());
        
        $incrementedBy10 = $duration->incrementBy(10);
        $this->assertEquals(110, $incrementedBy10->toSeconds());
        
        $this->assertEquals(100, $duration->toSeconds()); // Original should be unchanged
    }
    
    /**
     * Test incrementByDuration method
     */
    public function testIncrementByDuration(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        
        $incremented = $duration1->incrementByDuration($duration2);
        $this->assertEquals(150, $incremented->toSeconds());
        $this->assertEquals(100, $duration1->toSeconds()); // Original should be unchanged
        $this->assertEquals(50, $duration2->toSeconds()); // Second duration should be unchanged
    }
    
    /**
     * Test decrement method
     */
    public function testDecrement(): void
    {
        $duration = new Duration(100);
        $decremented = $duration->decrement();
        
        $this->assertEquals(99, $decremented->toSeconds());
        $this->assertEquals(100, $duration->toSeconds()); // Original should be unchanged
        
        // Test exception when decrementing from 0
        $zeroDuration = new Duration(0);
        $this->expectException(\InvalidArgumentException::class);
        $zeroDuration->decrement();
    }
    
    /**
     * Test decrementBy method
     */
    public function testDecrementBy(): void
    {
        $duration = new Duration(100);
        
        $decrementedBy1 = $duration->decrementBy(); // Default is 1
        $this->assertEquals(99, $decrementedBy1->toSeconds());
        
        $decrementedBy10 = $duration->decrementBy(10);
        $this->assertEquals(90, $decrementedBy10->toSeconds());
        
        $this->assertEquals(100, $duration->toSeconds()); // Original should be unchanged
        
        // Test exception when result would be negative
        $this->expectException(\InvalidArgumentException::class);
        $duration->decrementBy(200);
    }
    
    /**
     * Test decrementByDuration method
     */
    public function testDecrementByDuration(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        
        $decremented = $duration1->decrementByDuration($duration2);
        $this->assertEquals(50, $decremented->toSeconds());
        $this->assertEquals(100, $duration1->toSeconds()); // Original should be unchanged
        $this->assertEquals(50, $duration2->toSeconds()); // Second duration should be unchanged
        
        // Test exception when result would be negative
        $duration3 = new Duration(200);
        $this->expectException(\InvalidArgumentException::class);
        $duration1->decrementByDuration($duration3);
    }
    
    /**
     * Test safeDecrement method
     */
    public function testSafeDecrement(): void
    {
        $duration = new Duration(100);
        $decremented = $duration->safeDecrement();
        
        $this->assertEquals(99, $decremented->toSeconds());
        
        // Test safe decrement from 0 (should stay at 0)
        $zeroDuration = new Duration(0);
        $safeDecremented = $zeroDuration->safeDecrement();
        $this->assertEquals(0, $safeDecremented->toSeconds());
    }
    
    /**
     * Test safeDecrementBy method
     */
    public function testSafeDecrementBy(): void
    {
        $duration = new Duration(100);
        
        // Normal decrement
        $decrementedBy50 = $duration->safeDecrementBy(50);
        $this->assertEquals(50, $decrementedBy50->toSeconds());
        
        // Decrement by more than available (should return 0)
        $decrementedBy200 = $duration->safeDecrementBy(200);
        $this->assertEquals(0, $decrementedBy200->toSeconds());
        
        $this->assertEquals(100, $duration->toSeconds()); // Original should be unchanged
    }
    
    /**
     * Test safeDecrementByDuration method
     */
    public function testSafeDecrementByDuration(): void
    {
        $duration1 = new Duration(100);
        $duration2 = new Duration(50);
        $duration3 = new Duration(200);
        
        // Normal decrement
        $decremented = $duration1->safeDecrementByDuration($duration2);
        $this->assertEquals(50, $decremented->toSeconds());
        
        // Decrement by more than available (should return 0)
        $decrementedTooMuch = $duration1->safeDecrementByDuration($duration3);
        $this->assertEquals(0, $decrementedTooMuch->toSeconds());
        
        $this->assertEquals(100, $duration1->toSeconds()); // Original should be unchanged
    }
    
    /**
     * Test comparison with invalid mode throws exception
     */
    public function testInvalidComparisonMode(): void
    {
        $duration = new Duration(100);
        $otherDuration = new Duration(50);
        
        $this->expectException(\InvalidArgumentException::class);
        $duration->equals($otherDuration, 'invalid_mode');
    }
    
    /**
     * Test comparison with array containing non-Duration objects
     */
    public function testComparisonWithInvalidArray(): void
    {
        $duration = new Duration(100);
        $invalidArray = [new Duration(50), 'not a duration', new Duration(200)];
        
        $this->expectException(\InvalidArgumentException::class);
        $duration->equals($invalidArray);
    }
}
