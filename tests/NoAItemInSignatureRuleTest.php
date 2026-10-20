<?php

/**
 * PHPUnit tests for the PHPStan rule that passes aItem ids instead of instances.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules\Tests;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use Timmit\Phpstan\Rules\NoAItemInSignatureRule;

/**
 * PHPUnit tests for NoAItemInSignatureRule.php
 *
 * @extends RuleTestCase<NoAItemInSignatureRule>
 */
class NoAItemInSignatureRuleTest extends RuleTestCase
{
    use ReportsIdentifiersPerLine;

    protected function getRule(): Rule
    {
        return new NoAItemInSignatureRule();
    }

    public function testFlagsAItemParametersAndProperties(): void
    {
        $errors = $this->gatherAnalyserErrors([__DIR__ . '/Fixture/AItemInSignatureFixture.php']);

        /* Client is not an aItem; a return type and a local instance are allowed. */
        $this->assertSame(
            [
                13 => ['timmit.noAItemProperty'],
                19 => ['timmit.noAItemInSignature'],
                24 => ['timmit.noAItemInSignature'],
                28 => ['timmit.noAItemInSignature'],
                32 => ['timmit.noAItemInSignature'],
                36 => ['timmit.noAItemInSignature'],
                50 => ['timmit.noAItemInSignature'],
            ],
            $this->reportedIdentifiersPerLine($errors)
        );
    }
}
