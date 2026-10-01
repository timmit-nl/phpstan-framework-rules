<?php

/**
 * PHPStan rule: use Functions::isPopulatedString() instead of !== '' on a string.
 *
 * '' === stays allowed. It is true only for the empty string, while
 * !Functions::isPopulatedString() is also true for null and non-strings.
 * ($expr ?? '') !== '' stays on NoEmptyCoalesceStringComparisonRule.
 *
 * Registered in rules.neon. Existing hits are listed in phpstan-baseline.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<NotIdentical>
 */
class NoEmptyStringInequalityRule implements Rule
{
    private const MESSAGE = 'Use Functions::isPopulatedString() instead of comparing a string to \'\' with !==.';

    public function getNodeType(): string
    {
        return NotIdentical::class;
    }

    /**
     * @param NotIdentical $node
     * @param Scope $scope
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $other = $this->otherOperand($node);
        if (null === $other || $this->isEmptyStringCoalesce($other)) {
            return [];
        }

        if (!$scope->getType($other)->isString()->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier('timmit.noEmptyStringInequality')
                ->build(),
        ];
    }

    private function otherOperand(NotIdentical $node): ?Expr
    {
        if ($node->left instanceof String_ && '' === $node->left->value) {
            return $node->right;
        }

        if ($node->right instanceof String_ && '' === $node->right->value) {
            return $node->left;
        }

        return null;
    }

    private function isEmptyStringCoalesce(Expr $expr): bool
    {
        if ($expr instanceof StringCast) {
            $expr = $expr->expr;
        }

        if (!$expr instanceof Coalesce) {
            return false;
        }

        return $expr->right instanceof String_ && '' === $expr->right->value;
    }
}
