<?php

/**
 * Fixture for NoShorthandRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

/**
 * Query stand-in so nullsafe calls type-check without adding unrelated errors.
 */
class ShorthandQuery
{
    public string $name = 'query';

    /**
     * @return list<string>
     */
    public function toArray(): array
    {
        return [$this->name];
    }
}

/**
 * Exercises shorthand that must fail, and null coalescing that must stay allowed.
 */
class ShorthandFixture
{
    public function coalesceAssign(?string $value): string
    {
        $value ??= 'default';

        return $value;
    }

    public function coalesce(?string $value): string
    {
        return $value ?? 'default';
    }

    public function ternary(bool $active): string
    {
        return $active ? 'a' : 'b';
    }

    public function elvis(?string $value): string
    {
        return $value ?: 'b';
    }

    public function nestedTernary(bool $active, bool $ready): string
    {
        return ($active ? ($ready ? 'a' : 'b') : 'c');
    }

    public function nullsafeMethod(?ShorthandQuery $query): array
    {
        return $query?->toArray() ?? [];
    }

    public function nullsafeProperty(?ShorthandQuery $query): mixed
    {
        return $query?->name;
    }

    public function arrow(array $values): array
    {
        return array_map(static fn (mixed $value): mixed => $value, $values);
    }

    public function matchStatus(int $status): string
    {
        return match ($status) {
            1 => 'one',
            default => 'other',
        };
    }

    public function nullable(?string $value): ?string
    {
        return $value;
    }
}
