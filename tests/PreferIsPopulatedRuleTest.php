<?php

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TiMMiT\PHPStan\Rules\PreferIsPopulatedRule;

/**
 * @extends RuleTestCase<PreferIsPopulatedRule>
 */
class PreferIsPopulatedRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new PreferIsPopulatedRule();
    }

    public function testFlagsHandWrittenContentChecks(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/PreferIsPopulatedFixture.php']);

        $this->assertSame(
            [
                18 => ['timmit.preferIsPopulated'],
                23 => ['timmit.preferIsPopulated'],
                31 => ['timmit.preferIsPopulated'],
                36 => ['timmit.preferIsPopulated'],
                41 => ['timmit.preferIsPopulated'],
                46 => ['timmit.preferIsPopulated'],
                51 => ['timmit.preferIsPopulated'],
                56 => ['timmit.preferIsPopulated'],
                62 => ['timmit.preferIsPopulated'],
                76 => ['timmit.preferIsPopulated'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }

    public function testLeavesTheHelpersThemselvesAlone(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Stub/Functions.php']);

        $this->assertSame([], $this->reportedIdentifiersPerLine($errors));
    }

    public function testNamesTheHelperInTheMessage(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/PreferIsPopulatedFixture.php']);

        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getLine()] = $error->getMessage();
        }

        $this->assertStringStartsWith('Use Functions::isPopulatedArray()', $messages[18]);
        $this->assertStringStartsWith('Use !Functions::isPopulatedArray()', $messages[41]);
        $this->assertStringStartsWith('Use Functions::isPopulatedString()', $messages[51]);
        $this->assertStringStartsWith('Use !Functions::isPopulatedString()', $messages[56]);
        $this->assertStringContainsString('foreach', $messages[62]);
        $this->assertStringContainsString('[]', $messages[76]);
    }
}
