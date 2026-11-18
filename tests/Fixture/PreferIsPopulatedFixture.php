<?php

/**
 * Fixture for PreferIsPopulatedRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

/**
 * Exercises hand-written content checks next to type checks that must stay.
 */
class PreferIsPopulatedFixture
{
    public function badArrayWithCount(mixed $value): bool
    {
        return is_array($value) && 0 < count($value);
    }

    public function badArrayWithEmptyArray(mixed $value): bool
    {
        return is_array($value) && [] !== $value;
    }

    /**
     * @param array<string, mixed> $post
     */
    public function badArrayWithCoalesce(array $post): bool
    {
        return is_array($post['ids'] ?? null) && count($post['ids']) > 0;
    }

    public function badArrayInLongerChain(mixed $value, bool $enabled): bool
    {
        return $enabled && is_array($value) && 0 < count($value);
    }

    public function badNotArrayOrNoCount(mixed $value): bool
    {
        return !is_array($value) || 0 === count($value);
    }

    public function badFalseArrayOrEmptyArray(mixed $value): bool
    {
        return false === is_array($value) || [] === $value;
    }

    public function badStringWithStrlen(mixed $value): bool
    {
        return is_string($value) && 0 < strlen($value);
    }

    public function badNotStringOrEmptyString(mixed $value): bool
    {
        return !is_string($value) || '' === $value;
    }

    public function badForeachGuard(mixed $keys): int
    {
        $total = 0;
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $total = $total + 1;
            }
        }

        return $total;
    }

    /**
     * @return array<mixed>
     */
    public function badResetToEmptyArray(mixed $keys): array
    {
        if (false === is_array($keys)) {
            $keys = [];
        }

        return $keys;
    }

    /**
     * @return array<mixed>
     */
    public function goodTypeCheck(mixed $id): array
    {
        if (is_array($id)) {
            return $id;
        }

        return [$id];
    }

    public function goodForeachGuardWithMore(mixed $keys): int
    {
        $total = 0;
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $total = $total + 1;
            }
            $total = $total * 2;
        }

        return $total;
    }

    public function goodForeachGuardWithElse(mixed $keys): int
    {
        $total = 0;
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $total = $total + 1;
            }
        } else {
            $total = -1;
        }

        return $total;
    }

    /**
     * @return array<mixed>
     */
    public function goodResetWithMore(mixed $keys, bool $changed): array
    {
        if (!is_array($keys)) {
            $keys = [];
            $changed = true;
        }

        return $changed ? $keys : [];
    }

    /**
     * @param array<mixed> $other
     */
    public function goodCountOfOtherValue(mixed $value, array $other): bool
    {
        return is_array($value) && 0 < count($other);
    }

    public function goodCountOfOne(mixed $value): bool
    {
        return is_array($value) && 1 === count($value);
    }

    public function goodStringInequalityStaysOnOtherRule(mixed $value): bool
    {
        return is_string($value) && '' !== $value;
    }

    public function goodNotArrayOrHasCount(mixed $value): bool
    {
        return !is_array($value) || 0 < count($value);
    }
}
