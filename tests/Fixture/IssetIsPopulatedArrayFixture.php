<?php

/**
 * Fixture for NoIssetWithIsPopulatedArrayRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

use TiMMiT\PHPStan\Rules\Tests\Stub\Functions;

/**
 * Exercises isset + isPopulatedArray combination patterns.
 */
class IssetIsPopulatedArrayFixture
{
    /**
     * @param array<string, mixed> $filters
     */
    public function badIssetFirst(array $filters, string $type): bool
    {
        return !isset($filters[$type]) || !Functions::isPopulatedArray($filters[$type]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function badPopulatedFirst(array $filters, string $type): bool
    {
        return !Functions::isPopulatedArray($filters[$type]) || !isset($filters[$type]);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function goodWithNullCoalesce(array $filters, string $type): bool
    {
        return !Functions::isPopulatedArray($filters[$type] ?? null);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function differentIssetKeys(array $payload): bool
    {
        return !Functions::isPopulatedArray($payload) || !isset($payload['employee_list_1'], $payload['employee_list_2']);
    }
}
