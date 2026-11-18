<?php

/**
 * PHPStan rule: do not assign '' inside if (!Functions::isPopulatedString($var)) — return early instead.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class NoEmptyStringResetAfterIsPopulatedStringRule implements Rule
{
    private const MESSAGE = 'Do not assign \'\' when !Functions::isPopulatedString($var); return early instead of resetting to an empty string.';

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
        if (!$node instanceof If_) {
            return [];
        }

        $checkedVariable = $this->getNegatedIsPopulatedStringVariable($node->cond);
        if (null === $checkedVariable) {
            return [];
        }

        if (!$this->ifBodyAssignsEmptyStringToVariable($node, $checkedVariable)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier('timmit.noEmptyStringResetAfterIsPopulatedString')
                ->build(),
        ];
    }

    private function getNegatedIsPopulatedStringVariable(Node $condition): ?string
    {
        if (!$condition instanceof BooleanNot || !$condition->expr instanceof StaticCall) {
            return null;
        }

        $call = $condition->expr;
        if (!$this->isFunctionsIsPopulatedStringCall($call)) {
            return null;
        }

        $argument = $call->args[0]->value ?? null;
        if (!$argument instanceof Variable || !is_string($argument->name)) {
            return null;
        }

        return $argument->name;
    }

    private function isFunctionsIsPopulatedStringCall(StaticCall $call): bool
    {
        if (!$call->name instanceof Identifier || 'isPopulatedString' !== $call->name->toString()) {
            return false;
        }

        if (!$call->class instanceof Name) {
            return false;
        }

        $className = $call->class->toString();

        return 'Functions' === $className || str_ends_with($className, '\\Functions');
    }

    private function ifBodyAssignsEmptyStringToVariable(If_ $node, string $variableName): bool
    {
        foreach ($node->stmts ?? [] as $statement) {
            if ($this->isEmptyStringAssignToVariable($statement, $variableName)) {
                return true;
            }
        }

        return false;
    }

    private function isEmptyStringAssignToVariable(Node $statement, string $variableName): bool
    {
        if (!$statement instanceof Expression || !$statement->expr instanceof Assign) {
            return false;
        }

        $assign = $statement->expr;
        if (!$assign->var instanceof Variable || !is_string($assign->var->name)) {
            return false;
        }

        if ($variableName !== $assign->var->name) {
            return false;
        }

        return $assign->expr instanceof String_ && '' === $assign->expr->value;
    }
}
