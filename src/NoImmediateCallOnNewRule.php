<?php

/**
 * PHPStan rule: do not call a method or read a property directly on a new expression.
 *
 * PHP-Parser does not keep parentheses, so this covers both (new Foo())->bar() and new Foo()->bar().
 * Registered in rules.neon. Existing hits are listed in phpstan-baseline.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoImmediateCallOnNewRule implements Rule
{
    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @param Node $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall
            && !$node instanceof NullsafeMethodCall
            && !$node instanceof PropertyFetch
            && !$node instanceof NullsafePropertyFetch
        ) {
            return [];
        }

        if (!$node->var instanceof New_) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Do not call a method or read a property directly on new. Assign the instance to a variable first.'
            )
                ->identifier('timmit.noImmediateCallOnNew')
                ->build(),
        ];
    }
}
