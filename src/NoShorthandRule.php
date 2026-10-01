<?php

/**
 * PHPStan rule: disallow shorthand operators other than null coalescing (??).
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\AssignOp\Coalesce as CoalesceAssign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoShorthandRule implements Rule
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
        if ($node instanceof CoalesceAssign) {
            return [$this->error(
                'Do not use ??=. Assign the default in an if (is_null()) block.',
                'timmit.noNullCoalesceAssign'
            )];
        }

        if ($node instanceof Ternary) {
            return [$this->error(
                'Do not use the ternary operator (? : or ?:). Use an if/else block.',
                'timmit.noTernary'
            )];
        }

        if ($node instanceof NullsafeMethodCall || $node instanceof NullsafePropertyFetch) {
            return [$this->error(
                'Do not use the nullsafe operator (?->). Check is_null() before accessing the method or property.',
                'timmit.noNullsafeOperator'
            )];
        }

        if ($node instanceof ArrowFunction) {
            return [$this->error(
                'Do not use arrow functions (fn () =>). Use a foreach or a named method.',
                'timmit.noArrowFunction'
            )];
        }

        if ($node instanceof Match_) {
            return [$this->error(
                'Do not use match. Use if/elseif.',
                'timmit.noMatch'
            )];
        }

        return [];
    }

    /**
     * @return \PHPStan\Rules\IdentifierRuleError
     */
    private function error(string $message, string $identifier): \PHPStan\Rules\IdentifierRuleError
    {
        return RuleErrorBuilder::message($message)
            ->identifier($identifier)
            ->build();
    }
}
