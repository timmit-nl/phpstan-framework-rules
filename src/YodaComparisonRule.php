<?php

/**
 * PHPStan rule: require Yoda-style === and !== when comparing to a scalar constant.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class YodaComparisonRule implements Rule
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
        if (!$node instanceof Identical && !$node instanceof NotIdentical) {
            return [];
        }

        if ($this->isConstantOrLiteral($node->left)) {
            return [];
        }

        if (!$this->isConstantOrLiteral($node->right)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Use Yoda-style comparison: put the literal or constant on the left (e.g. 0 === count($items)).'
            )
                ->identifier('timmit.yodaComparison')
                ->build(),
        ];
    }

    private function isConstantOrLiteral(Node $node): bool
    {
        if ($node instanceof UnaryMinus || $node instanceof UnaryPlus) {
            return $this->isNumericLiteral($node->expr);
        }

        return $this->isNumericLiteral($node)
            || $node instanceof String_
            || $node instanceof ConstFetch
            || $node instanceof ClassConstFetch;
    }

    /**
     * Integer and float literals, including the operand of a signed literal such as -1.
     */
    private function isNumericLiteral(Node $node): bool
    {
        return $node instanceof Int_ || $node instanceof Float_;
    }
}
