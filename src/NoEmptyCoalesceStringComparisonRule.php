<?php

/**
 * PHPStan rule: use Functions::isPopulatedString($expr ?? null) instead of comparing ($expr ?? '') to ''.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoEmptyCoalesceStringComparisonRule implements Rule
{
    private const MESSAGE_NOT_EMPTY = 'Use Functions::isPopulatedString($expr ?? null) instead of comparing ($expr ?? \'\') to \'\'.';

    private const MESSAGE_EMPTY = 'Use !Functions::isPopulatedString($expr ?? null) instead of comparing ($expr ?? \'\') to \'\'.';

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

        if (null === $this->extractEmptyCoalesceExpr($node)) {
            return [];
        }

        $message = self::MESSAGE_EMPTY;
        if ($node instanceof NotIdentical) {
            $message = self::MESSAGE_NOT_EMPTY;
        }

        return [
            RuleErrorBuilder::message($message)
                ->identifier('timmit.noEmptyCoalesceStringComparison')
                ->build(),
        ];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    private function extractEmptyCoalesceExpr($node): ?Node
    {
        if ($node->left instanceof String_ && '' === $node->left->value) {
            return $this->unwrapEmptyStringCoalesce($node->right);
        }

        if ($node->right instanceof String_ && '' === $node->right->value) {
            return $this->unwrapEmptyStringCoalesce($node->left);
        }

        return null;
    }

    private function unwrapEmptyStringCoalesce(Node $node): ?Node
    {
        $expr = $node;
        if ($expr instanceof StringCast) {
            $expr = $expr->expr;
        }

        if (!$expr instanceof Coalesce) {
            return null;
        }

        if (!$expr->right instanceof String_ || '' !== $expr->right->value) {
            return null;
        }

        return $expr->left;
    }
}
