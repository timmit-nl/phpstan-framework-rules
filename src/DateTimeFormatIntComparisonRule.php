<?php

/**
 * PHPStan rule: compare numeric DateTime::format() parts as int, not string literals.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\Cast\Int_ as IntCast;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class DateTimeFormatIntComparisonRule implements Rule
{
    /** @var list<string> */
    private const NUMERIC_FORMAT_SPECS = [
        'd', 'j', 'm', 'n', 'H', 'h', 'G', 'g', 'i', 's', 'Y', 'y', 'w', 'N',
    ];

    private const MESSAGE = 'Compare DateTime format() numeric parts as integers: use 1 === (int)$reference->format(\'d\'), not \'01\' === $reference->format(\'d\').';

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

        if ($this->isValidIntFormatComparison($node->left, $node->right)) {
            return [];
        }

        $formatCall = $this->getFormatCallWithoutIntCast($node->left) ?? $this->getFormatCallWithoutIntCast($node->right);
        $numericLiteral = $this->getNumericStringLiteral($node->left) ?? $this->getNumericStringLiteral($node->right);

        if (null === $formatCall || null === $numericLiteral) {
            return [];
        }

        if (!$this->isNumericFormatCall($formatCall)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier('timmit.dateTimeFormatIntComparison')
                ->build(),
        ];
    }

    private function isValidIntFormatComparison(Node $left, Node $right): bool
    {
        $formatCall = $this->getFormatCallWithIntCast($left) ?? $this->getFormatCallWithIntCast($right);
        $intLiteral = $this->getIntLiteral($left) ?? $this->getIntLiteral($right);

        if (null === $formatCall || null === $intLiteral) {
            return false;
        }

        return $this->isNumericFormatCall($formatCall);
    }

    private function getFormatCallWithoutIntCast(Node $node): ?MethodCall
    {
        if ($node instanceof IntCast) {
            return null;
        }

        return $this->getFormatCall($node);
    }

    private function getFormatCallWithIntCast(Node $node): ?MethodCall
    {
        if ($node instanceof IntCast) {
            return $this->getFormatCall($node->expr);
        }

        return null;
    }

    private function getFormatCall(Node $node): ?MethodCall
    {
        if (!$node instanceof MethodCall) {
            return null;
        }

        if (!$node->name instanceof Identifier || 'format' !== $node->name->toString()) {
            return null;
        }

        return $node;
    }

    private function isNumericFormatCall(MethodCall $call): bool
    {
        $formatArg = $call->args[0]->value ?? null;
        if (!$formatArg instanceof String_) {
            return false;
        }

        return in_array($formatArg->value, self::NUMERIC_FORMAT_SPECS, true);
    }

    private function getNumericStringLiteral(Node $node): ?String_
    {
        if (!$node instanceof String_) {
            return null;
        }

        if ('' === $node->value || !ctype_digit($node->value)) {
            return null;
        }

        return $node;
    }

    private function getIntLiteral(Node $node): ?Int_
    {
        if ($node instanceof Int_) {
            return $node;
        }

        return null;
    }
}
