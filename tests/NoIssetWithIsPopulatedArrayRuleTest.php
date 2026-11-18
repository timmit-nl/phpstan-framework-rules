<?php

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\NoIssetWithIsPopulatedArrayRule;

/**
 * @extends RuleTestCase<NoIssetWithIsPopulatedArrayRule>
 */
class NoIssetWithIsPopulatedArrayRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoIssetWithIsPopulatedArrayRule();
    }

    public function testFlagsIssetWithIsPopulatedArrayOnSameExpression(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/IssetIsPopulatedArrayFixture.php']);

        $this->assertSame(
            [
                23 => ['timmit.noIssetWithIsPopulatedArray'],
                31 => ['timmit.noIssetWithIsPopulatedArray'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
