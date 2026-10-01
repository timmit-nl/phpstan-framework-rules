<?php

/**
 * Fixture for NoCountOnAItemBaseRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use Timmit\Phpstan\Rules\Tests\Stub\Functions;

/**
 * Sample aItem-like entity for static analysis fixtures.
 */
class ExampleAItemEntity
{
    /** @var array<string, array<string, mixed>> */
    public static array $array = [];
}

/**
 * Exercises loaded-check patterns for NoCountOnAItemBaseRule.
 */
class AItemBaseCountFixture
{
    public function countOnBaseDirect(string $id): bool
    {
        return 0 === count(ExampleAItemEntity::$array[$id]['base']);
    }

    public function countOnBaseWithCoalesce(string $id): bool
    {
        return count(ExampleAItemEntity::$array[$id]['base'] ?? []) === 0;
    }

    public function countOnBaseGreaterThanZero(string $id): bool
    {
        return count(ExampleAItemEntity::$array[$id]['base'] ?? []) > 0;
    }

    public function populatedArrayOnBase(string $id): bool
    {
        return Functions::isPopulatedArray(ExampleAItemEntity::$array[$id]['base'] ?? null);
    }

    public function populatedArrayOnBaseNegated(string $id): bool
    {
        return !Functions::isPopulatedArray(ExampleAItemEntity::$array[$id]['base'] ?? null);
    }

    public function countOnNestedBaseField(string $id): bool
    {
        return count(ExampleAItemEntity::$array[$id]['base']['field'] ?? []) === 0;
    }

    public function countOnValues(string $id): bool
    {
        return count(ExampleAItemEntity::$array[$id]['values'] ?? []) === 0;
    }

    public function populatedArrayOnValues(string $id): bool
    {
        return Functions::isPopulatedArray(ExampleAItemEntity::$array[$id]['values'] ?? null);
    }
}
