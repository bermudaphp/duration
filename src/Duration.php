<?php

namespace Bermuda\Stdlib;

/**
 * Class Duration
 * 
 * An immutable wrapper for working with time durations with ISO 8601 support.
 * 
 * ISO 8601 duration format: P[n]Y[n]M[n]DT[n]H[n]M[n]S
 * Where:
 * - P is the duration designator (for period) placed at the start
 * - Y is the year designator
 * - M is the month designator
 * - D is the day designator
 * - T is the time designator (required if any time components are used)
 * - H is the hour designator
 * - M is the minute designator
 * - S is the second designator
 * 
 * Example: P3Y6M4DT12H30M5S represents a duration of 3 years, 6 months, 4 days, 12 hours, 30 minutes, and 5 seconds.
 */
final class Duration implements \Stringable, \JsonSerializable
{
    /**
     * @var int Number of seconds in a minute
     */
    private const SECONDS_IN_MINUTE = 60;
    
    /**
     * @var int Number of seconds in an hour
     */
    private const SECONDS_IN_HOUR = 3600;
    
    /**
     * @var int Number of seconds in a day
     */
    private const SECONDS_IN_DAY = 86400;
    
    /**
     * @var int Number of seconds in a week
     */
    private const SECONDS_IN_WEEK = 604800;
    
    /**
     * @var int Approximate number of seconds in a month (30.44 days)
     */
    private const SECONDS_IN_MONTH = 2629746;
    
    /**
     * @var int Approximate number of seconds in a year (365.25 days)
     */
    private const SECONDS_IN_YEAR = 31557600;
    
    /**
     * The total duration in seconds
     */
    private readonly int $seconds;
    
    /**
     * The years component of the duration
     */
    private int $years {
        get {
            return (int)($this->seconds / self::SECONDS_IN_YEAR);
        }
    }
    
    /**
     * The months component of the duration (excluding years)
     */
    private int $months {
        get {
            $remainingSeconds = $this->seconds % self::SECONDS_IN_YEAR;
            return (int)($remainingSeconds / self::SECONDS_IN_MONTH);
        }
    }
    
    /**
     * The days component of the duration (excluding years and months)
     */
    private int $days {
        get {
            $remainingSeconds = $this->seconds % self::SECONDS_IN_YEAR;
            $remainingSeconds = $remainingSeconds % self::SECONDS_IN_MONTH;
            return (int)($remainingSeconds / self::SECONDS_IN_DAY);
        }
    }
    
    /**
     * The hours component of the duration (excluding years, months, and days)
     */
    private int $hours {
        get {
            $remainingSeconds = $this->seconds % self::SECONDS_IN_DAY;
            return (int)($remainingSeconds / self::SECONDS_IN_HOUR);
        }
    }
    
    /**
     * The minutes component of the duration (excluding years, months, days, and hours)
     */
    private int $minutes {
        get {
            $remainingSeconds = $this->seconds % self::SECONDS_IN_HOUR;
            return (int)($remainingSeconds / self::SECONDS_IN_MINUTE);
        }
    }
    
    /**
     * The seconds component of the duration (excluding years, months, days, hours, and minutes)
     */
    private int $remainingSeconds {
        get {
            return $this->seconds % self::SECONDS_IN_MINUTE;
        }
    }
    
    /**
     * Constructs a new Duration instance
     * 
     * @param int $seconds The total duration in seconds
     */
    public function __construct(int $seconds = 0)
    {
        $this->seconds = max(0, $seconds); // Ensure non-negative duration
    }
    
    /**
     * Creates a Duration from the specified number of years
     * 
     * @param int $years The number of years
     * @return self
     */
    public static function fromYears(int $years): self
    {
        return new self($years * self::SECONDS_IN_YEAR);
    }
    
    /**
     * Creates a Duration from the specified number of months
     * 
     * @param int $months The number of months
     * @return self
     */
    public static function fromMonths(int $months): self
    {
        return new self($months * self::SECONDS_IN_MONTH);
    }
    
    /**
     * Creates a Duration from the specified number of weeks
     * 
     * @param int $weeks The number of weeks
     * @return self
     */
    public static function fromWeeks(int $weeks): self
    {
        return new self($weeks * self::SECONDS_IN_WEEK);
    }
    
    /**
     * Creates a Duration from the specified number of days
     * 
     * @param int $days The number of days
     * @return self
     */
    public static function fromDays(int $days): self
    {
        return new self($days * self::SECONDS_IN_DAY);
    }
    
    /**
     * Creates a Duration from the specified number of hours
     * 
     * @param int $hours The number of hours
     * @return self
     */
    public static function fromHours(int $hours): self
    {
        return new self($hours * self::SECONDS_IN_HOUR);
    }
    
    /**
     * Creates a Duration from the specified number of minutes
     * 
     * @param int $minutes The number of minutes
     * @return self
     */
    public static function fromMinutes(int $minutes): self
    {
        return new self($minutes * self::SECONDS_IN_MINUTE);
    }
    
    /**
     * Creates a Duration from the specified number of seconds
     * 
     * @param int $seconds The number of seconds
     * @return self
     */
    public static function fromSeconds(int $seconds): self
    {
        return new self($seconds);
    }
    
    /**
     * Validates if a string is a valid ISO 8601 duration format
     * 
     * @param string $iso8601Duration The ISO 8601 duration string to validate
     * @return bool True if the string is a valid ISO 8601 duration, false otherwise
     */
    public static function validate(string $iso8601Duration): bool
    {
        // Basic format check: P must be at start, and if T is present, it must separate date and time parts
        if (!preg_match('/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?(\d+S)?)?$/', $iso8601Duration)) {
            return false;
        }
        
        // If there's a T, there must be at least one time component after it
        if (strpos($iso8601Duration, 'T') !== false && !preg_match('/T.*[HMS]/', $iso8601Duration)) {
            return false;
        }
        
        // The duration shouldn't be empty (P or PT alone)
        if ($iso8601Duration === 'P' || $iso8601Duration === 'PT') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Creates a Duration instance from an ISO 8601 duration string
     * 
     * @param string $iso8601Duration The ISO 8601 duration string (e.g., P1Y2M3DT4H5M6S)
     * @return self
     * @throws \InvalidArgumentException If the duration string is invalid
     */
    public static function fromISO8601(string $iso8601Duration): self
    {
        if (!self::validate($iso8601Duration)) {
            throw new \InvalidArgumentException("Invalid ISO 8601 duration format: $iso8601Duration");
        }
        
        // Extract the duration components
        preg_match('/^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?$/', $iso8601Duration, $matches);
        
        $years = isset($matches[1]) ? (int)$matches[1] : 0;
        $months = isset($matches[2]) ? (int)$matches[2] : 0;
        $days = isset($matches[3]) ? (int)$matches[3] : 0;
        $hours = isset($matches[4]) ? (int)$matches[4] : 0;
        $minutes = isset($matches[5]) ? (int)$matches[5] : 0;
        $seconds = isset($matches[6]) ? (int)$matches[6] : 0;
        
        // Calculate the total duration in seconds
        $totalSeconds = 
            $years * self::SECONDS_IN_YEAR +
            $months * self::SECONDS_IN_MONTH +
            $days * self::SECONDS_IN_DAY +
            $hours * self::SECONDS_IN_HOUR +
            $minutes * self::SECONDS_IN_MINUTE +
            $seconds;
            
        return new self($totalSeconds);
    }
    
    /**
     * Creates a Duration instance from a DateInterval
     * 
     * @param \DateInterval $interval The DateInterval instance
     * @return self
     */
    public static function fromDateInterval(\DateInterval $interval): self
    {
        $seconds = 
            $interval->y * self::SECONDS_IN_YEAR +
            $interval->m * self::SECONDS_IN_MONTH +
            $interval->d * self::SECONDS_IN_DAY +
            $interval->h * self::SECONDS_IN_HOUR +
            $interval->i * self::SECONDS_IN_MINUTE +
            $interval->s;
            
        return new self($seconds);
    }
    
    /**
     * Creates a Duration instance representing the difference between two DateTimeInterface objects
     * 
     * @param \DateTimeInterface $start The start date
     * @param \DateTimeInterface $end The end date
     * @return self
     */
    public static function between(\DateTimeInterface $start, \DateTimeInterface $end): self
    {
        $seconds = $end->getTimestamp() - $start->getTimestamp();
        return new self($seconds);
    }
    
    /**
     * Returns the total duration in seconds
     * 
     * @return int The total duration in seconds
     */
    public function toSeconds(): int
    {
        return $this->seconds;
    }
    
    /**
     * Returns the total duration in minutes (rounded down)
     * 
     * @return int The total duration in minutes
     */
    public function toMinutes(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_MINUTE);
    }
    
    /**
     * Returns the total duration in hours (rounded down)
     * 
     * @return int The total duration in hours
     */
    public function toHours(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_HOUR);
    }
    
    /**
     * Returns the total duration in days (rounded down)
     * 
     * @return int The total duration in days
     */
    public function toDays(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_DAY);
    }
    
    /**
     * Returns the total duration in weeks (rounded down)
     * 
     * @return int The total duration in weeks
     */
    public function toWeeks(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_WEEK);
    }
    
    /**
     * Returns the total duration in months (rounded down)
     * 
     * @return int The total duration in months
     */
    public function toMonths(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_MONTH);
    }
    
    /**
     * Returns the total duration in years (rounded down)
     * 
     * @return int The total duration in years
     */
    public function toYears(): int
    {
        return (int)($this->seconds / self::SECONDS_IN_YEAR);
    }
    
    /**
     * Adds the specified number of years to this duration
     * 
     * @param int $years The number of years to add
     * @return self A new Duration instance
     */
    public function addYears(int $years): self
    {
        return new self($this->seconds + ($years * self::SECONDS_IN_YEAR));
    }
    
    /**
     * Adds the specified number of months to this duration
     * 
     * @param int $months The number of months to add
     * @return self A new Duration instance
     */
    public function addMonths(int $months): self
    {
        return new self($this->seconds + ($months * self::SECONDS_IN_MONTH));
    }
    
    /**
     * Adds the specified number of weeks to this duration
     * 
     * @param int $weeks The number of weeks to add
     * @return self A new Duration instance
     */
    public function addWeeks(int $weeks): self
    {
        return new self($this->seconds + ($weeks * self::SECONDS_IN_WEEK));
    }
    
    /**
     * Adds the specified number of days to this duration
     * 
     * @param int $days The number of days to add
     * @return self A new Duration instance
     */
    public function addDays(int $days): self
    {
        return new self($this->seconds + ($days * self::SECONDS_IN_DAY));
    }
    
    /**
     * Adds the specified number of hours to this duration
     * 
     * @param int $hours The number of hours to add
     * @return self A new Duration instance
     */
    public function addHours(int $hours): self
    {
        return new self($this->seconds + ($hours * self::SECONDS_IN_HOUR));
    }
    
    /**
     * Adds the specified number of minutes to this duration
     * 
     * @param int $minutes The number of minutes to add
     * @return self A new Duration instance
     */
    public function addMinutes(int $minutes): self
    {
        return new self($this->seconds + ($minutes * self::SECONDS_IN_MINUTE));
    }
    
    /**
     * Adds the specified number of seconds to this duration
     * 
     * @param int $seconds The number of seconds to add
     * @return self A new Duration instance
     */
    public function addSeconds(int $seconds): self
    {
        return new self($this->seconds + $seconds);
    }
    
    /**
     * Subtracts the specified number of years from this duration
     * 
     * @param int $years The number of years to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractYears(int $years): self
    {
        $newSeconds = $this->seconds - ($years * self::SECONDS_IN_YEAR);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $years . ' years from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of months from this duration
     * 
     * @param int $months The number of months to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractMonths(int $months): self
    {
        $newSeconds = $this->seconds - ($months * self::SECONDS_IN_MONTH);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $months . ' months from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of weeks from this duration
     * 
     * @param int $weeks The number of weeks to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractWeeks(int $weeks): self
    {
        $newSeconds = $this->seconds - ($weeks * self::SECONDS_IN_WEEK);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $weeks . ' weeks from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of days from this duration
     * 
     * @param int $days The number of days to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractDays(int $days): self
    {
        $newSeconds = $this->seconds - ($days * self::SECONDS_IN_DAY);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $days . ' days from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of hours from this duration
     * 
     * @param int $hours The number of hours to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractHours(int $hours): self
    {
        $newSeconds = $this->seconds - ($hours * self::SECONDS_IN_HOUR);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $hours . ' hours from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of minutes from this duration
     * 
     * @param int $minutes The number of minutes to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractMinutes(int $minutes): self
    {
        $newSeconds = $this->seconds - ($minutes * self::SECONDS_IN_MINUTE);
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $minutes . ' minutes from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Subtracts the specified number of seconds from this duration
     * 
     * @param int $seconds The number of seconds to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function subtractSeconds(int $seconds): self
    {
        $newSeconds = $this->seconds - $seconds;
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract ' . $seconds . ' seconds from this duration (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Converts the duration to an associative array of its non-zero component parts
     * 
     * Returns an array with only the non-zero individual components of the duration:
     * - 'years' - Years component (if non-zero)
     * - 'months' - Months component (if non-zero)
     * - 'days' - Days component (if non-zero)
     * - 'hours' - Hours component (if non-zero)
     * - 'minutes' - Minutes component (if non-zero)
     * - 'seconds' - Seconds component (if non-zero)
     * 
     * If all components are zero, returns an array with just seconds set to 0.
     * 
     * @return array The filtered array representation of the duration components
     */
    public function toArray(): array
    {
        $components = [
            'years' => $this->years,
            'months' => $this->months,
            'days' => $this->days,
            'hours' => $this->hours,
            'minutes' => $this->minutes,
            'seconds' => $this->remainingSeconds,
        ];
        
        // Filter out zero values
        $filtered = array_filter($components, fn($value) => $value > 0);
        
        // If all components are zero, return at least the seconds component
        if (empty($filtered)) {
            return ['seconds' => 0];
        }
        
        return $filtered;
    }
    
    /**
     * Converts the duration to a human-readable time format string
     * 
     * Returns a time string in the format:
     * - "MM:SS" for durations less than 1 hour
     * - "HH:MM:SS" for durations less than 1 day
     * - "DD:HH:MM:SS" for durations less than 30 days
     * - "MM:DD:HH:MM:SS" for durations less than 12 months (MM = months)
     * - "YY:MM:DD:HH:MM:SS" for longer durations (YY = years)
     * 
     * The method properly converts between units:
     * - 60 seconds = 1 minute
     * - 60 minutes = 1 hour
     * - 24 hours = 1 day
     * - 30 days = 1 month
     * - 12 months = 1 year
     * 
     * @return string The formatted time string
     */
    public function toHumanReadable(): string
    {
        // Start with the total seconds
        $totalSeconds = $this->seconds;
        
        // Extract years, months, days, hours, minutes, seconds
        $years = (int)($totalSeconds / self::SECONDS_IN_YEAR);
        $totalSeconds %= self::SECONDS_IN_YEAR;
        
        $months = (int)($totalSeconds / self::SECONDS_IN_MONTH);
        $totalSeconds %= self::SECONDS_IN_MONTH;
        
        $days = (int)($totalSeconds / self::SECONDS_IN_DAY);
        $totalSeconds %= self::SECONDS_IN_DAY;
        
        $hours = (int)($totalSeconds / self::SECONDS_IN_HOUR);
        $totalSeconds %= self::SECONDS_IN_HOUR;
        
        $minutes = (int)($totalSeconds / self::SECONDS_IN_MINUTE);
        $seconds = $totalSeconds % self::SECONDS_IN_MINUTE;
        
        // Build parts from right to left
        $parts = [];
        
        // Always include seconds
        $parts[] = str_pad((string)$seconds, 2, '0', STR_PAD_LEFT);
        
        // Always include minutes
        array_unshift($parts, str_pad((string)$minutes, 2, '0', STR_PAD_LEFT));
        
        // Include hours if they are non-zero or if there are higher units
        if ($hours > 0 || $days > 0 || $months > 0 || $years > 0) {
            array_unshift($parts, str_pad((string)$hours, 2, '0', STR_PAD_LEFT));
        }
        
        // Include days if they are non-zero or if there are higher units
        if ($days > 0 || $months > 0 || $years > 0) {
            array_unshift($parts, str_pad((string)$days, 2, '0', STR_PAD_LEFT));
        }
        
        // Include months if they are non-zero or if there are years
        if ($months > 0 || $years > 0) {
            array_unshift($parts, str_pad((string)$months, 2, '0', STR_PAD_LEFT));
        }
        
        // Include years if they are non-zero
        if ($years > 0) {
            array_unshift($parts, str_pad((string)$years, 2, '0', STR_PAD_LEFT));
        }
        
        return implode(':', $parts);
    }
    
    /**
     * Returns a new Duration that is the sum of this duration and the other
     * 
     * @param Duration $other The other duration
     * @return self A new Duration instance
     */
    public function add(Duration $other): self
    {
        return new self($this->seconds + $other->seconds);
    }
    
    /**
     * Returns a new Duration that is the difference of this duration and the other
     * 
     * @param Duration $other The other duration
     * @return self A new Duration instance
     */
    public function subtract(Duration $other): self
    {
        return new self(max(0, $this->seconds - $other->seconds));
    }
    
    /**
     * Safely subtracts another duration from this duration, returning zero duration if the result would be negative
     * 
     * Unlike the subtract() method which limits the result to zero, this method throws an exception
     * if the subtraction would result in a negative duration.
     * 
     * @param Duration $other The duration to subtract
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the result would be negative
     */
    public function strictSubtract(Duration $other): self
    {
        $newSeconds = $this->seconds - $other->seconds;
        if ($newSeconds < 0) {
            throw new \InvalidArgumentException('Cannot subtract a larger duration from a smaller one (would result in negative duration)');
        }
        return new self($newSeconds);
    }
    
    /**
     * Returns a new Duration that is this duration multiplied by the given factor
     * 
     * @param int|float $factor The factor to multiply by
     * @return self A new Duration instance
     */
    public function multiply(int|float $factor): self
    {
        return new self((int)($this->seconds * $factor));
    }
    
    /**
     * Returns a new Duration that is this duration divided by the given divisor
     * 
     * @param int|float $divisor The divisor to divide by
     * @return self A new Duration instance
     * @throws \InvalidArgumentException If the divisor is zero
     */
    public function divide(int|float $divisor): self
    {
        if ($divisor == 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        
        return new self((int)($this->seconds / $divisor));
    }
    
    /**
     * Compares this duration with another duration
     * 
     * @param Duration $other The other duration
     * @return int Returns < 0 if this duration is less than the other, 
     *             > 0 if this duration is greater than the other, 
     *             0 if they are equal
     */
    public function compareTo(Duration $other): int
    {
        return $this->seconds <=> $other->seconds;
    }
    
    /**
     * Checks if this duration equals another duration or at least one duration in an array
     * 
     * @param Duration|array<Duration> $other One or more Duration objects to compare against
     * @return bool True if the durations match, false otherwise
     */
    public function equals(Duration|array $other): bool
    {
        if ($other instanceof Duration) {
            return $this->seconds === $other->seconds;
        }
        
        foreach ($other as $duration) {
            if (!$duration instanceof Duration) {
                throw new \InvalidArgumentException('Array must contain only Duration objects');
            }
            
            if ($this->seconds === $duration->seconds) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Checks if this duration is less than another duration or all durations in an array
     * 
     * @param Duration|array<Duration> $other One or more Duration objects to compare against
     * @return bool True if this duration is less than the other(s), false otherwise
     */
    public function lessThan(Duration|array $other): bool
    {
        if ($other instanceof Duration) {
            return $this->seconds < $other->seconds;
        }
        
        if (empty($other)) {
            return false;
        }
        
        foreach ($other as $duration) {
            if (!$duration instanceof Duration) {
                throw new \InvalidArgumentException('Array must contain only Duration objects');
            }
            
            if ($this->seconds >= $duration->seconds) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Checks if this duration is less than or equal to another duration or all durations in an array
     * 
     * @param Duration|array<Duration> $other One or more Duration objects to compare against
     * @return bool True if this duration is less than or equal to the other(s), false otherwise
     */
    public function lessThanOrEqual(Duration|array $other): bool
    {
        if ($other instanceof Duration) {
            return $this->seconds <= $other->seconds;
        }
        
        if (empty($other)) {
            return false;
        }
        
        foreach ($other as $duration) {
            if (!$duration instanceof Duration) {
                throw new \InvalidArgumentException('Array must contain only Duration objects');
            }
            
            if ($this->seconds > $duration->seconds) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Checks if this duration is greater than another duration or all durations in an array
     * 
     * @param Duration|array<Duration> $other One or more Duration objects to compare against
     * @return bool True if this duration is greater than the other(s), false otherwise
     */
    public function greaterThan(Duration|array $other): bool
    {
        if ($other instanceof Duration) {
            return $this->seconds > $other->seconds;
        }
        
        if (empty($other)) {
            return false;
        }
        
        foreach ($other as $duration) {
            if (!$duration instanceof Duration) {
                throw new \InvalidArgumentException('Array must contain only Duration objects');
            }
            
            if ($this->seconds <= $duration->seconds) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Checks if this duration is greater than or equal to another duration or all durations in an array
     * 
     * @param Duration|array<Duration> $other One or more Duration objects to compare against
     * @return bool True if this duration is greater than or equal to the other(s), false otherwise
     */
    public function greaterThanOrEqual(Duration|array $other): bool
    {
        if ($other instanceof Duration) {
            return $this->seconds >= $other->seconds;
        }
        
        if (empty($other)) {
            return false;
        }
        
        foreach ($other as $duration) {
            if (!$duration instanceof Duration) {
                throw new \InvalidArgumentException('Array must contain only Duration objects');
            }
            
            if ($this->seconds < $duration->seconds) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Checks if this duration is between two durations (inclusive)
     * 
     * @param Duration $min The minimum duration
     * @param Duration $max The maximum duration
     * @param bool $inclusive Whether the comparison should be inclusive (default: true)
     * @return bool True if this duration is between min and max, false otherwise
     */
    public function between(Duration $min, Duration $max, bool $inclusive = true): bool
    {
        if ($inclusive) {
            return $this->seconds >= $min->seconds && $this->seconds <= $max->seconds;
        } else {
            return $this->seconds > $min->seconds && $this->seconds < $max->seconds;
        }
    }
    
    /**
     * Returns a formatted string representation of the duration
     * 
     * @param string $format The format string (e.g., '%H:%I:%S')
     *                      %Y - years
     *                      %M - months
     *                      %D - days
     *                      %H - hours
     *                      %I - minutes
     *                      %S - seconds
     *                      %T - total seconds
     * @return string The formatted string
     */
    public function format(string $format): string
    {
        $replacements = [
            '%Y' => str_pad((string)$this->years, 2, '0', STR_PAD_LEFT),
            '%M' => str_pad((string)$this->months, 2, '0', STR_PAD_LEFT),
            '%D' => str_pad((string)$this->days, 2, '0', STR_PAD_LEFT),
            '%H' => str_pad((string)$this->hours, 2, '0', STR_PAD_LEFT),
            '%I' => str_pad((string)$this->minutes, 2, '0', STR_PAD_LEFT),
            '%S' => str_pad((string)$this->remainingSeconds, 2, '0', STR_PAD_LEFT),
            '%T' => (string)$this->seconds,
        ];
        
        return strtr($format, $replacements);
    }
    
    /**
     * Converts the duration to an ISO 8601 duration string
     * 
     * @return string The ISO 8601 duration string
     */
    public function toISO8601(): string
    {
        $result = 'P';
        
        if ($this->years > 0) {
            $result .= $this->years . 'Y';
        }
        
        if ($this->months > 0) {
            $result .= $this->months . 'M';
        }
        
        if ($this->days > 0) {
            $result .= $this->days . 'D';
        }
        
        $hasTime = $this->hours > 0 || $this->minutes > 0 || $this->remainingSeconds > 0;
        
        if ($hasTime) {
            $result .= 'T';
            
            if ($this->hours > 0) {
                $result .= $this->hours . 'H';
            }
            
            if ($this->minutes > 0) {
                $result .= $this->minutes . 'M';
            }
            
            if ($this->remainingSeconds > 0) {
                $result .= $this->remainingSeconds . 'S';
            }
        }
        
        // If the duration is zero, return P0S
        if ($result === 'P') {
            return 'PT0S';
        }
        
        return $result;
    }
    
    /**
     * Converts the duration to a DateInterval object
     * 
     * @return \DateInterval The DateInterval object
     */
    public function toDateInterval(): \DateInterval
    {
        $interval = new \DateInterval('PT' . $this->seconds . 'S');
        $interval->y = $this->years;
        $interval->m = $this->months;
        $interval->d = $this->days;
        $interval->h = $this->hours;
        $interval->i = $this->minutes;
        $interval->s = $this->remainingSeconds;
        
        return $interval;
    }
    
    /**
     * Implements the Stringable interface
     * 
     * @return string The ISO 8601 representation of the duration
     */
    public function __toString(): string
    {
        return $this->toISO8601();
    }
    
    /**
     * Implements the JsonSerializable interface
     * 
     * @return mixed The value to be JSON encoded
     */
    public function jsonSerialize(): mixed
    {
        return [
            'seconds' => $this->seconds,
            'iso8601' => $this->toISO8601(),
            'components' => [
                'years' => $this->years,
                'months' => $this->months,
                'days' => $this->days,
                'hours' => $this->hours,
                'minutes' => $this->minutes,
                'seconds' => $this->remainingSeconds,
            ]
        ];
    }
}
