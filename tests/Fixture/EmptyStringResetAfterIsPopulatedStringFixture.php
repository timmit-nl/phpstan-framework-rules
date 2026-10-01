<?php

/**
 * Fixture for NoEmptyStringResetAfterIsPopulatedStringRuleTest, analysed by PHPStan and never executed.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests\Fixture;

use Timmit\Phpstan\Rules\Tests\Stub\Functions;

/**
 * Exercises empty-string reset after failed isPopulatedString checks.
 */
class EmptyStringResetAfterIsPopulatedStringFixture
{
    public function badDoubleCheck(string $polis_id): array
    {
        $relatie_id = $this->getBaseFieldOf($polis_id, 'relatie_id');
        if (!Functions::isPopulatedString($relatie_id)) {
            $relatie_id = '';
        }
        if (!Functions::isPopulatedString($relatie_id)) {
            return [];
        }

        return ['relatie_id' => $relatie_id];
    }

    public function badSingleReset(string $polis_id): array
    {
        $relatie_id = $this->getBaseFieldOf($polis_id, 'relatie_id');
        if (!Functions::isPopulatedString($relatie_id)) {
            $relatie_id = '';
        }

        return ['relatie_id' => $relatie_id];
    }

    public function goodEarlyReturn(string $polis_id): array
    {
        $relatie_id = $this->getBaseFieldOf($polis_id, 'relatie_id');
        if (!Functions::isPopulatedString($relatie_id)) {
            return [];
        }

        return ['relatie_id' => $relatie_id];
    }

    public function goodDifferentVariable(string $polis_id): array
    {
        $relatie_id = $this->getBaseFieldOf($polis_id, 'relatie_id');
        if (!Functions::isPopulatedString($relatie_id)) {
            $other_id = '';
        }

        return ['relatie_id' => $relatie_id];
    }

    private function getBaseFieldOf(string $id, string $field): ?string
    {
        return null;
    }
}
