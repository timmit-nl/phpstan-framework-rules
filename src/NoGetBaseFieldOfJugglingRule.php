<?php

/**
 * PHPStan rule: no (string) on getBaseFieldOf(); no (int)/(float) on status; no ?? '' / ?? null;
 * use getGroupIdOf() instead of getBaseFieldOf(..., 'group_id').
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_ as IntCast;
use PhpParser\Node\Expr\Cast\String_ as StringCast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoGetBaseFieldOfJugglingRule implements Rule
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
        if ($node instanceof Cast) {
            return $this->checkCast($node, $scope);
        }

        if ($node instanceof Coalesce) {
            return $this->checkNullCoalesce($node, $scope);
        }

        if ($node instanceof MethodCall || $node instanceof NullsafeMethodCall) {
            return $this->checkMethodCall($node, $scope);
        }

        return [];
    }

    /**
     * @param MethodCall|NullsafeMethodCall $call
     *
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkMethodCall($call, Scope $scope): array
    {
        if (!$this->isGetBaseFieldOfCall($call, $scope)) {
            return [];
        }

        if (!$this->isGroupIdField($this->getFieldNameFromCall($call))) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Use getGroupIdOf($id) instead of getBaseFieldOf($id, \'group_id\').'
            )
                ->identifier('timmit.noGetBaseFieldOfGroupId')
                ->build(),
        ];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkCast(Cast $node, Scope $scope): array
    {
        if (!$node->expr instanceof MethodCall && !$node->expr instanceof NullsafeMethodCall) {
            return [];
        }

        $call = $node->expr;
        if (!$this->isGetBaseFieldOfCall($call, $scope)) {
            return [];
        }

        $fieldName = $this->getFieldNameFromCall($call);

        if ($this->isGroupIdField($fieldName)) {
            return [];
        }

        if ($node instanceof StringCast) {
            return [
                RuleErrorBuilder::message(
                    '(string) is never useful on getBaseFieldOf(); it returns string|null. Use Functions::isPopulatedString() or return null.'
                )
                    ->identifier('timmit.noGetBaseFieldOfStringCast')
                    ->build(),
            ];
        }

        if ($this->isStatusField($fieldName) && ($node instanceof IntCast || $node instanceof Double)) {
            $castLabel = '(int)';
            if ($node instanceof Double) {
                $castLabel = '(float)';
            }

            return [
                RuleErrorBuilder::message(
                    $castLabel . ' on getBaseFieldOf(..., \'status\') is not allowed; use getStatusAsIntOf() or getStatusOf() instead.'
                )
                    ->identifier('timmit.noGetBaseFieldOfStatusCast')
                    ->build(),
            ];
        }

        if ($node instanceof IntCast || $node instanceof Double) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Avoid casting getBaseFieldOf(); only keep casts when truly unavoidable — never (string) or numeric cast on status.'
            )
                ->identifier('timmit.noGetBaseFieldOfCastDiscouraged')
                ->build(),
        ];
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    private function checkNullCoalesce(Coalesce $node, Scope $scope): array
    {
        if (!$node->left instanceof MethodCall && !$node->left instanceof NullsafeMethodCall) {
            return [];
        }

        if (!$this->isGetBaseFieldOfCall($node->left, $scope)) {
            return [];
        }

        if ($node->right instanceof String_ && '' === $node->right->value) {
            return [
                RuleErrorBuilder::message(
                    'Do not use getBaseFieldOf() ?? \'\'. Check with Functions::isPopulatedString() or return null.'
                )
                    ->identifier('timmit.noGetBaseFieldOfEmptyCoalesce')
                    ->build(),
            ];
        }

        if ($node->right instanceof ConstFetch && 'null' === $node->right->name->toLowerString()) {
            return [
                RuleErrorBuilder::message(
                    'Do not use getBaseFieldOf() ?? null; the call already returns null when the field is missing.'
                )
                    ->identifier('timmit.noGetBaseFieldOfNullCoalesce')
                    ->build(),
            ];
        }

        return [];
    }

    /**
     * @param MethodCall|NullsafeMethodCall $call
     */
    private function isGetBaseFieldOfCall($call, Scope $scope): bool
    {
        if (!$call->name instanceof Identifier) {
            return false;
        }

        if ('getBaseFieldOf' !== $call->name->toString()) {
            return false;
        }

        /* aItem exposes getGroupIdOf(). Only flag a receiver that certainly has that accessor. */
        if (!$scope->getType($call->var)->hasMethod('getGroupIdOf')->yes()) {
            return false;
        }

        return true;
    }

    /**
     * Resolves the field-name argument, including calls that pass it by name.
     *
     * @param MethodCall|NullsafeMethodCall $call
     */
    private function getFieldNameFromCall($call): ?string
    {
        foreach ($call->args as $argument) {
            if (!$argument instanceof Arg || !$argument->name instanceof Identifier) {
                continue;
            }

            if ('field' !== strtolower($argument->name->toString())) {
                continue;
            }

            return $this->stringLiteralValue($argument->value);
        }

        $positional = $call->args[1] ?? null;
        if (!$positional instanceof Arg || $positional->name instanceof Identifier) {
            return null;
        }

        return $this->stringLiteralValue($positional->value);
    }

    private function stringLiteralValue(Node $node): ?string
    {
        if (!$node instanceof String_) {
            return null;
        }

        return $node->value;
    }

    private function isStatusField(?string $fieldName): bool
    {
        return null !== $fieldName && 'status' === strtolower($fieldName);
    }

    private function isGroupIdField(?string $fieldName): bool
    {
        return null !== $fieldName && 'group_id' === strtolower($fieldName);
    }
}
