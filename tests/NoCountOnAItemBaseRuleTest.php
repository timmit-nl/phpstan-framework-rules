<?php

/**
 * PHPUnit tests for the PHPStan rule that prefers isLoaded() over count on ['base'].
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\NoCountOnAItemBaseRule;

/**
 * PHPUnit tests for NoCountOnAItemBaseRule.php
 *
 * @extends RuleTestCase<NoCountOnAItemBaseRule>
 */
class NoCountOnAItemBaseRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoCountOnAItemBaseRule();
    }

    public function testFlagsCountOnAItemBase(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/AItemBaseCountFixture.php']);

        $this->assertSame(
            [
                29 => ['timmit.noCountOnAItemBase'],
                34 => ['timmit.noCountOnAItemBase'],
                39 => ['timmit.noCountOnAItemBase'],
                44 => ['timmit.noPopulatedArrayOnAItemBase'],
                49 => ['timmit.noPopulatedArrayOnAItemBase'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
