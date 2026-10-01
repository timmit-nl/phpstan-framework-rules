<?php

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoEmptyStringResetAfterIsPopulatedStringRule;

/**
 * @extends RuleTestCase<NoEmptyStringResetAfterIsPopulatedStringRule>
 */
class NoEmptyStringResetAfterIsPopulatedStringRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoEmptyStringResetAfterIsPopulatedStringRule();
    }

    public function testFlagsEmptyStringResetAfterIsPopulatedString(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/EmptyStringResetAfterIsPopulatedStringFixture.php']);

        $this->assertSame(
            [
                21 => ['timmit.noEmptyStringResetAfterIsPopulatedString'],
                34 => ['timmit.noEmptyStringResetAfterIsPopulatedString'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
