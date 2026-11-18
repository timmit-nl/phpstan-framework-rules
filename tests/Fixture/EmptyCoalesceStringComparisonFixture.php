<?php

/**
 * Fixture for NoEmptyCoalesceStringComparisonRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests\Fixture;

use TiMMiT\PHPStan\Rules\Tests\Stub\Functions;
use TiMMiT\PHPStan\Rules\Tests\Stub\Groups;

/**
 * Exercises empty-string comparisons against ($expr ?? '').
 */
class EmptyCoalesceStringComparisonFixture
{
    /**
     * @param array<string, mixed> $values
     */
    public function badWithStringCast(string $company_id, array $values): bool
    {
        return '' !== (string)($values['emailTextBlock']['mv7xxAfzender'] ?? '');
    }

    /**
     * @param array<string, mixed> $values
     */
    public function badWithoutStringCast(string $company_id, array $values): bool
    {
        return '' !== ($values['emailTextBlock']['mv7xxAfzender'] ?? '');
    }

    /**
     * @param array<string, mixed> $values
     */
    public function badEmptyCheck(string $company_id, array $values): bool
    {
        return '' === ($values['emailTextBlock']['mv7xxAfzender'] ?? '');
    }

    /**
     * @param array<string, mixed> $values
     */
    public function badReversed(string $company_id, array $values): bool
    {
        return ($values['emailTextBlock']['mv7xxAfzender'] ?? '') !== '';
    }

    public function goodIsPopulatedString(string $company_id): bool
    {
        return Functions::isPopulatedString(Groups::$array[$company_id]['values']['emailTextBlock']['mv7xxAfzender'] ?? null);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function goodNegated(string $company_id, array $values): bool
    {
        return !Functions::isPopulatedString($values['emailTextBlock']['mv7xxAfzender'] ?? null);
    }
}
