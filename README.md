# Duration

An immutable PHP class for handling time durations with ISO 8601 support for PHP 8.4+.

## Overview

`Duration` is a part of the `Bermuda\Stdlib` namespace and provides a comprehensive solution for working with time durations in PHP. This class offers a clean, object-oriented approach to create, manipulate, and format time intervals.

## Features

- **Immutable Design**: All operations return new instances without modifying the original
- **ISO 8601 Compliance**: Full support for creating and formatting durations using ISO 8601 standard
- **Computed Properties**: Dynamically calculated time components (years, months, days, etc.)
- **Time Unit Conversion**: Convert between different time units
- **Arithmetic Operations**: Add, subtract, multiply, and divide durations
- **Flexible Comparison**: Compare durations with single instances or arrays of durations
- **Flexible Formatting**: Format durations as ISO 8601 strings or custom formats
- **PHP 8.4 Features**: Leverages computed properties and other PHP 8.4 features

## Requirements

- PHP 8.4 or higher

## Installation

```bash
composer require bermudaphp/duration
```

## Usage

### Creating Durations

```php
use Bermuda\Stdlib\Duration;

// Empty duration (0 seconds)
$zeroDuration = new Duration();

// From seconds
$duration = new Duration(3600); // 1 hour

// From specific units using static methods
$oneYear = Duration::fromYears(1);
$twoMonths = Duration::fromMonths(2);
$threeWeeks = Duration::fromWeeks(3);
$fourDays = Duration::fromDays(4);
$fiveHours = Duration::fromHours(5);
$sixMinutes = Duration::fromMinutes(6);
$sevenSeconds = Duration::fromSeconds(7);

// From ISO 8601 duration string
$isoDuration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// From DateInterval
$interval = new \DateInterval('P1Y2M');
$durationFromInterval = Duration::fromDateInterval($interval);

// Between two dates
$start = new \DateTime('2023-01-01');
$end = new \DateTime('2023-02-01');
$between = Duration::between($start, $end);
```

### Validating ISO 8601 Format

```php
// Check if a string is a valid ISO 8601 duration
$isValid = Duration::validate('P1Y2M3D'); // true
$isInvalid = Duration::validate('1Y2M3D'); // false (missing 'P')
```

### Getting Duration Components

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Access components as properties
$years = $duration->years;         // 1
$months = $duration->months;       // 2
$days = $duration->days;           // 3
$hours = $duration->hours;         // 4
$minutes = $duration->minutes;     // 5
$seconds = $duration->remainingSeconds; // 6

// Get components as an array (only non-zero values)
$components = $duration->toArray();
// ['years' => 1, 'months' => 2, 'days' => 3, 'hours' => 4, 'minutes' => 5, 'seconds' => 6]
```

### Converting to Different Units

```php
$duration = Duration::fromDays(1);

$inSeconds = $duration->toSeconds();  // 86400
$inMinutes = $duration->toMinutes();  // 1440
$inHours = $duration->toHours();      // 24
$inDays = $duration->toDays();        // 1
$inWeeks = $duration->toWeeks();      // 0 (less than 1 week)
$inMonths = $duration->toMonths();    // 0 (less than 1 month)
$inYears = $duration->toYears();      // 0 (less than 1 year)
```

### Modifying Durations

```php
$duration = Duration::fromHours(1);

// Adding time units (returns new instance)
$plusYears = $duration->addYears(1);
$plusMonths = $duration->addMonths(2);
$plusWeeks = $duration->addWeeks(3);
$plusDays = $duration->addDays(4);
$plusHours = $duration->addHours(5);
$plusMinutes = $duration->addMinutes(6);
$plusSeconds = $duration->addSeconds(7);

// Adding another duration
$sum = $duration->add(Duration::fromMinutes(30));

// Subtracting time units (throws exception if result would be negative)
try {
    $minusHours = $duration->subtractHours(1);  // Works as duration is 1 hour
    $minusHours = $duration->subtractHours(2);  // Throws exception
} catch (\InvalidArgumentException $e) {
    // Handle exception
}

// Safe subtraction (returns zero duration if result would be negative)
$diff = $duration->subtract(Duration::fromHours(2));  // Returns zero duration

// Strict subtraction (throws exception if result would be negative)
try {
    $strictDiff = $duration->strictSubtract(Duration::fromHours(2));  // Throws exception
} catch (\InvalidArgumentException $e) {
    // Handle exception
}

// Multiplication and division
$doubled = $duration->multiply(2);
$halved = $duration->divide(2);
```

### Comparing Durations

```php
$duration = Duration::fromHours(2);
$shorter = Duration::fromHours(1);
$longer = Duration::fromHours(3);
$equal = Duration::fromHours(2);

// Compare with a single duration
$duration->equals($equal);              // true
$duration->lessThan($longer);           // true
$duration->greaterThan($shorter);       // true
$duration->lessThanOrEqual($equal);     // true
$duration->greaterThanOrEqual($equal);  // true

// Compare with an array of durations
$duration->equals([$shorter, $equal, $longer]);  // true (equals at least one in the array)
$duration->lessThan([$longer, Duration::fromHours(4)]);  // true (less than all in array)
$duration->greaterThan([$shorter, Duration::fromMinutes(30)]);  // true (greater than all in array)

// Check if duration is between two values
$duration->between($shorter, $longer);       // true (inclusive by default)
$duration->between($shorter, $equal, true);  // true (inclusive)
$duration->between($shorter, $equal, false); // false (exclusive)

// Compare using compareTo (returns int)
$compareResult = $duration->compareTo($shorter);  // positive value (greater than)
$compareResult = $duration->compareTo($equal);    // 0 (equal)
$compareResult = $duration->compareTo($longer);   // negative value (less than)
```

### Formatting Durations

```php
$duration = Duration::fromISO8601('P1Y2M3DT4H5M6S');

// Convert to ISO 8601 string
$iso8601 = $duration->toISO8601();  // "P1Y2M3DT4H5M6S"

// Human-readable time format
$humanReadable = $duration->toHumanReadable();  // "01:02:03:04:05:06"

// Custom formatting with placeholders
$custom = $duration->format('%Y years, %M months, %D days, %H:%I:%S');
// "01 years, 02 months, 03 days, 04:05:06"

// Convert to DateInterval
$interval = $duration->toDateInterval();

// String conversion (uses ISO 8601)
$string = (string)$duration;  // "P1Y2M3DT4H5M6S"

// JSON serialization
$json = json_encode($duration);
// {"seconds":36993906,"iso8601":"P1Y2M3DT4H5M6S","components":{"years":1,"months":2,"days":3,"hours":4,"minutes":5,"seconds":6}}
```

## Immutability

The `Duration` class is immutable, which means once a duration instance is created, it cannot be changed. All methods that would modify the duration return a new instance instead, ensuring thread safety and preventing unexpected mutations.

```php
$duration = Duration::fromHours(1);
$newDuration = $duration->addMinutes(30);

echo $duration->toHumanReadable();   // "01:00:00"
echo $newDuration->toHumanReadable(); // "01:30:00"
```

## Exception Handling

Methods that could result in invalid durations throw exceptions:

```php
// Division by zero
try {
    $duration->divide(0);  // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    // Handle division by zero
}

// Negative duration in strict operations
try {
    $duration = Duration::fromHours(1);
    $duration->subtractHours(2);  // Throws InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    // Handle negative duration error
}
```

## ISO 8601 Duration Format

The class follows the ISO 8601 duration format, which is:

```
P[n]Y[n]M[n]DT[n]H[n]M[n]S
```

Where:
- `P` is the duration designator (for period) placed at the start
- `Y` is the year designator
- `M` is the month designator
- `D` is the day designator
- `T` is the time designator (required if any time components are used)
- `H` is the hour designator
- `M` is the minute designator
- `S` is the second designator

Example: `P3Y6M4DT12H30M5S` represents a duration of 3 years, 6 months, 4 days, 12 hours, 30 minutes, and 5 seconds.
