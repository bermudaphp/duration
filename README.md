# Duration

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](https://opensource.org/licenses/MIT)

*Read this in other languages: [Русский](README.ru.md)*

## Overview

`Duration` is an immutable wrapper class for working with time durations with ISO 8601 support. It provides a comprehensive API for creating, manipulating, formatting, and comparing time durations in a type-safe and consistent manner.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Factory Methods](#factory-methods)
- [ISO 8601 Support](#iso-8601-support)
- [Conversion Methods](#conversion-methods)
- [Arithmetic Operations](#arithmetic-operations)
- [Comparison Operations](#comparison-operations)
- [Increment and Decrement Operations](#increment-and-decrement-operations)
- [Formatting Methods](#formatting-methods)
- [Serialization](#serialization)
- [DateInterval Conversion](#dateinterval-conversion)
- [Examples](#examples)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Immutable Design**: All operations return new instances, preserving the original
- **Fluent Interface**: Method chaining for cleaner code
- **ISO 8601 Support**: Parse and generate ISO 8601 duration strings (e.g., `P1Y2M3DT4H5M6S`)
- **Comprehensive Time Units**: Convert between seconds, minutes, hours, days, weeks, months, and years
- **Advanced Comparison**: Compare durations with support for ALL/ANY modes when working with arrays
- **Increment/Decrement**: Convenient methods for increasing or decreasing durations
- **Multiple Formatting Options**: Human-readable, custom format templates, and component arrays
- **Serialization Support**: JSON serialization built-in
- **DateInterval Integration**: Convert to and from PHP's DateInterval class

## Installation

```bash
composer require bermuda/stdlib
```

## Basic Usage

```php
use Bermuda\Stdlib\Duration;

// Create a duration of 1 hour and 30 minutes
$duration = new Duration(5400);

// Get total seconds
echo $duration->toSeconds(); // 5400

// Convert to minutes
echo $duration->toMinutes(); // 90

// Convert to hours
echo $duration->toHours(); // 1

// Get the ISO 8601 representation
echo $duration->toISO8601(); // "PT1H30M"

// Get a human-readable representation
echo $duration->toHumanReadable(); // "01:30:00"
```

## Factory Methods

`Duration` provides multiple factory methods for creating instances:

```php
// From specific time units
$fromSeconds = Duration::fromSeconds(60);       // 60 seconds
$fromMinutes = Duration::fromMinutes(5);        // 5 minutes
$fromHours = Duration::fromHours(2);            // 2 hours
$fromDays = Duration::fromDays(1);              // 1 day
$fromWeeks = Duration::fromWeeks(2);            // 2 weeks
$fromMonths = Duration::fromMonths(3);          // 3 months
$fromYears = Duration::fromYears(1);            // 1 year

// From ISO 8601 string
$fromISO = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// From DateInterval
$interval = new \DateInterval('P1DT6H');
$fromInterval = Duration::fromDateInterval($interval);
```

## ISO 8601 Support

`Duration` fully supports the ISO 8601 duration format:

```php
// Validate ISO 8601 strings
$isValid = Duration::validate('P1Y2M3DT4H5M6S'); // true
$isInvalid = Duration::validate('P1X'); // false

// Create from ISO 8601 string
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Convert to ISO 8601 string
echo $duration->toISO8601(); // "P1Y2M3DT4H5M6S"
```

### ISO 8601 Format Rules

- Format: `P[n]Y[n]M[n]DT[n]H[n]M[n]S`
- `P` is the duration designator (for period) placed at the start
- `Y` is the year designator
- `M` is the month designator
- `D` is the day designator
- `T` is the time designator (required if any time components are used)
- `H` is the hour designator
- `M` is the minute designator
- `S` is the second designator

Example: `P3Y6M4DT12H30M5S` represents a duration of 3 years, 6 months, 4 days, 12 hours, 30 minutes, and 5 seconds.

## Conversion Methods

Convert a duration to different time units:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

echo $duration->toSeconds(); // Total seconds
echo $duration->toMinutes(); // Total minutes (rounded down)
echo $duration->toHours();   // Total hours (rounded down)
echo $duration->toDays();    // Total days (rounded down)
echo $duration->toWeeks();   // Total weeks (rounded down)
echo $duration->toMonths();  // Total months (rounded down)
echo $duration->toYears();   // Total years (rounded down)
```

## Arithmetic Operations

Perform arithmetic operations on durations:

```php
$duration1 = new Duration(3600); // 1 hour
$duration2 = new Duration(1800); // 30 minutes

// Addition
$sum = $duration1->add($duration2); // 1 hour 30 minutes

// Adding specific time units
$plusOneHour = $duration1->addHours(1);     // Add 1 hour
$plusTenMinutes = $duration1->addMinutes(10); // Add 10 minutes
$plusYear = $duration1->addYears(1);        // Add 1 year

// Subtraction (throws exception if result would be negative)
$difference = $duration1->strictSubtract($duration2); // 30 minutes

// Safe subtraction (returns zero if result would be negative)
$safeDifference = $duration1->subtract($duration2); // 30 minutes

// Subtracting specific time units (throws exception if result would be negative)
$minusOneHour = $duration2->subtractHours(1); // Exception!
$minusTenMinutes = $duration2->subtractMinutes(10); // 20 minutes

// Multiplication
$doubled = $duration1->multiply(2); // 2 hours

// Division
$halved = $duration1->divide(2); // 30 minutes
```

## Comparison Operations

Compare durations with various methods:

```php
$duration1 = new Duration(3600); // 1 hour
$duration2 = new Duration(7200); // 2 hours
$duration3 = new Duration(3600); // 1 hour

// Compare two durations
$comparisonResult = $duration1->compareTo($duration2); // -1 (less than)

// Equality
$isEqual = $duration1->equals($duration3); // true
$isNotEqual = $duration1->equals($duration2); // false

// Greater/less than
$isLess = $duration1->lessThan($duration2); // true
$isLessOrEqual = $duration1->lessThanOrEqual($duration3); // true
$isGreater = $duration2->greaterThan($duration1); // true
$isGreaterOrEqual = $duration1->greaterThanOrEqual($duration3); // true

// Check if between two durations
$isBetween = $duration1->between(
    new Duration(1800),  // 30 minutes
    new Duration(10800), // 3 hours
    true                // inclusive (default)
); // true
```

### Comparing with Arrays of Durations

Compare a duration against multiple durations using ALL or ANY modes:

```php
$duration = new Duration(3600); // 1 hour
$durations = [
    new Duration(1800),  // 30 minutes
    new Duration(7200),  // 2 hours
    new Duration(10800)  // 3 hours
];

// ALL mode (default) - the condition must be true for ALL durations in the array
$isLessThanAll = $duration->lessThan($durations, Duration::COMPARE_ALL); // false
$isGreaterThanAll = $duration->greaterThan($durations, Duration::COMPARE_ALL); // false

// ANY mode - the condition must be true for AT LEAST ONE duration in the array
$isLessThanAny = $duration->lessThan($durations, Duration::COMPARE_ANY); // false
$isGreaterThanAny = $duration->greaterThan($durations, Duration::COMPARE_ANY); // true
```

## Increment and Decrement Operations

Increment or decrement durations:

```php
$duration = Duration::fromMinutes(5); // 5 minutes

// Increment by 1 second
$incremented = $duration->increment(); // 5 minutes 1 second

// Increment by specific amount
$incrementedByMinute = $duration->incrementBy(60); // 6 minutes

// Increment by another duration
$anotherDuration = Duration::fromMinutes(10);
$combined = $duration->incrementByDuration($anotherDuration); // 15 minutes

// Decrement (throws exception if result would be negative)
$decremented = $duration->decrement(); // 4 minutes 59 seconds

// Decrement by specific amount (throws exception if result would be negative)
$decrementedByMinute = $duration->decrementBy(60); // 4 minutes

// Safe decrement (returns zero if result would be negative)
$safeDecremented = $duration->safeDecrement(); // 4 minutes 59 seconds

// Safe decrement by specific amount (returns zero if result would be negative)
$safeDecrementedByTooMuch = $duration->safeDecrementBy(600); // 0 seconds
```

## Formatting Methods

Format durations in different ways:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Convert to array of components
$array = $duration->toArray();
/*
[
    'years' => 1,
    'months' => 2,
    'days' => 3,
    'hours' => 4,
    'minutes' => 5,
    'seconds' => 6
]
*/

// Format using a custom template
$formatted = $duration->format('Years: %Y, Months: %M, Days: %D, Time: %H:%I:%S');
// "Years: 01, Months: 02, Days: 03, Time: 04:05:06"

// Human-readable format
$readable = $duration->toHumanReadable(); // "01:02:03:04:05:06"
```

### Format Placeholders

The `format()` method supports the following placeholders:

| Placeholder | Description |
|-------------|-------------|
| `%Y` | Years (zero-padded) |
| `%M` | Months (zero-padded) |
| `%D` | Days (zero-padded) |
| `%H` | Hours (zero-padded) |
| `%I` | Minutes (zero-padded) |
| `%S` | Seconds (zero-padded) |
| `%T` | Total seconds |

## Serialization

The `Duration` class implements `JsonSerializable` and `Stringable` interfaces:

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Convert to string (ISO 8601 format)
echo (string)$duration; // "P1Y2M3DT4H5M6S"

// JSON serialization
$json = json_encode($duration);
/*
{
    "seconds": 37090998,
    "iso8601": "P1Y2M3DT4H5M6S",
    "components": {
        "years": 1,
        "months": 2,
        "days": 3,
        "hours": 4,
        "minutes": 5,
        "seconds": 6
    }
}
*/
```

## DateInterval Conversion

Convert between `Duration` and PHP's `DateInterval`:

```php
// Create from DateInterval
$interval = new \DateInterval('P1YT6H');
$duration = Duration::fromDateInterval($interval);

// Convert to DateInterval
$newInterval = $duration->toDateInterval();
echo $newInterval->format('%y years, %h hours'); // "1 years, 6 hours"
```

## Examples

### Creating a Countdown Timer

```php
// Create a 5-minute countdown timer
$countdownDuration = Duration::fromMinutes(5);

// Simulate countdown (in a real application, this would be in a loop with sleep)
for ($i = 0; $i < 5; $i++) {
    echo "Remaining: " . $countdownDuration->toHumanReadable() . PHP_EOL;
    $countdownDuration = $countdownDuration->decrementBy(60); // Decrease by 1 minute
}

echo "Time's up!";
```

### Working with Events

```php
// Event duration
$eventDuration = Duration::fromHours(2);

// Check if event is longer than 1 hour but shorter than 3 hours
$isReasonableLength = $eventDuration->between(
    Duration::fromHours(1),
    Duration::fromHours(3)
);

// Create a 15-minute break
$breakDuration = Duration::fromMinutes(15);

// Add 15-minute break at the end
$totalDuration = $eventDuration->add($breakDuration);

echo "Total event duration: " . $totalDuration->toHumanReadable();
```

### Tracking Task Time

```php
// Start time stored in database as seconds
$taskTimeSeconds = 3600; // 1 hour already tracked
$taskDuration = new Duration($taskTimeSeconds);

// User works for 45 more minutes
$additionalTime = Duration::fromMinutes(45);
$updatedTaskDuration = $taskDuration->add($additionalTime);

// Get task time components for display
$components = $updatedTaskDuration->toArray();
$hours = $components['hours'] ?? 0;
$minutes = $components['minutes'] ?? 0;

echo "Task time: {$hours}h {$minutes}m";
```

## Testing

Run the PHPUnit tests:

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.
