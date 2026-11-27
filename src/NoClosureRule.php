<?php

/**
 * PHPStan rule: do not use a classical closure.
 *
 * Named methods and first-class callables ($this->method(...)) stay allowed.
 * The daemon callbacks in CLIRun are listed in phpstan-baseline.neon for now.
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure as AnonymousFunction;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<AnonymousFunction>
 */
class NoClosureRule implements Rule
{
    public function getNodeType(): string
    {
        return AnonymousFunction::class;
    }

    /**
     * @param AnonymousFunction $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        return [
            RuleErrorBuilder::message(
                'Do not use a closure (function () {}). Use a named method and pass it as a first-class callable.'
            )
                ->identifier('timmit.noClosure')
                ->build(),
        ];
    }
}
