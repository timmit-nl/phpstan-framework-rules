<?php

/**
 * Fixture for NoEmptyStringInequalityRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use Timmit\Phpstan\Rules\Tests\Stub\Functions;

/**
 * Exercises !== comparisons against ''.
 */
class EmptyStringInequalityFixture
{
    public function badYoda(string $segment): bool
    {
        return '' !== $segment;
    }

    public function badReversed(string $name): bool
    {
        return $name !== '';
    }

    public function allowedExactEmpty(string $segment): bool
    {
        return '' === $segment;
    }

    public function allowedMixed(mixed $value): bool
    {
        return '' !== $value;
    }

    public function allowedNullable(?string $value): bool
    {
        return '' !== $value;
    }

    /**
     * @param array<string, mixed> $values
     */
    public function allowedCoalesce(array $values): bool
    {
        return '' !== ($values['email'] ?? '');
    }

    public function good(string $segment): bool
    {
        return Functions::isPopulatedString($segment);
    }
}
