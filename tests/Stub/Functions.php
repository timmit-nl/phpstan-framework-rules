<?php

/**
 * Stand-in for Gull\Framework\Functions and TiMMiT\Mosquito\Functions.
 *
 * The rules match the short class name, so the stub only has to be called Functions.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Stub;

class Functions
{
    public static function isPopulatedString(mixed $value): bool
    {
        return is_string($value) && '' !== $value;
    }

    public static function isPopulatedArray(mixed $value): bool
    {
        return is_array($value) && 0 < count($value);
    }

    public static function createDateTimeFromFormat(string $format, string $value): \DateTime
    {
        return new \DateTime();
    }
}
