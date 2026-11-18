<?php

/**
 * PHPStan rule: use Functions::isPopulatedArray() / isPopulatedString() where a hand-written check means the same.
 *
 * Only forms that give the same result as the helper are reported:
 * - is_array($x) && 0 < count($x) or [] !== $x, and !is_array($x) || 0 === count($x) or [] === $x
 * - is_string($x) && 0 < strlen($x), and !is_string($x) || '' === $x or 0 === strlen($x)
 * - if (is_array($x)) { foreach ($x ...) } with nothing else, because a foreach over [] does nothing
 * - if (!is_array($x)) { $x = []; } with nothing else, because setting [] to [] changes nothing
 *
 * A bare is_array() or is_string() that tells types apart is not reported: the helper would treat
 * an empty value differently there. is_string($x) && '' !== $x stays on NoEmptyStringInequalityRule.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeDumper;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Node>
 */
class PreferIsPopulatedRule implements Rule
{
    private const IDENTIFIER = 'timmit.preferIsPopulated';

    private const MESSAGE_ARRAY = 'Use Functions::isPopulatedArray() instead of is_array() with a count() or [] check.';

    private const MESSAGE_NOT_ARRAY = 'Use !Functions::isPopulatedArray() instead of !is_array() with an empty count() or [] check.';

    private const MESSAGE_STRING = 'Use Functions::isPopulatedString() instead of is_string() with a strlen() check.';

    private const MESSAGE_NOT_STRING = 'Use !Functions::isPopulatedString() instead of !is_string() with an empty string or strlen() check.';

    private const MESSAGE_FOREACH = 'Use Functions::isPopulatedArray() for an is_array() guard around a foreach over the same value; a foreach over [] does nothing.';

    private const MESSAGE_RESET = 'Use !Functions::isPopulatedArray() when the body only sets the value to []; setting [] to [] changes nothing.';

    /**
     * Length comparisons written with the length call on the left: true means "has content", false means "empty".
     */
    private const LENGTH_CHECKS = [
        '> 0' => true,
        '>= 1' => true,
        '!== 0' => true,
        '!= 0' => true,
        '=== 0' => false,
        '== 0' => false,
        '< 1' => false,
        '<= 0' => false,
    ];

    /**
     * Operator to read when the length call is on the right, so 0 < count($x) reads as count($x) > 0.
     */
    private const MIRRORED_SIGILS = [
        '<' => '>',
        '>' => '<',
        '<=' => '>=',
        '>=' => '<=',
    ];

    private const ARRAY_LENGTH_FUNCTIONS = ['count', 'sizeof'];

    private const STRING_LENGTH_FUNCTIONS = ['strlen', 'mb_strlen'];

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
        $message = null;
        if ($node instanceof BooleanAnd) {
            $message = $this->messageForAnd($node);
        } elseif ($node instanceof BooleanOr) {
            $message = $this->messageForOr($node);
        } elseif ($node instanceof If_) {
            $message = $this->messageForIf($node);
        }

        if (null === $message || $this->isInsideHelper($scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message($message)
                ->identifier(self::IDENTIFIER)
                ->build(),
        ];
    }

    /**
     * Functions::isPopulatedArray() and isPopulatedString() are written with the very checks this rule reports.
     */
    private function isInsideHelper(Scope $scope): bool
    {
        $function = $scope->getFunction();
        $class = $scope->getClassReflection();
        if (null === $function || null === $class) {
            return false;
        }

        if (!str_ends_with('\\' . $class->getName(), '\\Functions')) {
            return false;
        }

        return in_array(strtolower($function->getName()), ['ispopulatedarray', 'ispopulatedstring'], true);
    }

    private function messageForAnd(BooleanAnd $node): ?string
    {
        foreach ($this->operandPairs($node) as [$typeCheck, $contentCheck]) {
            $checked = $this->functionArgument($typeCheck, ['is_array']);
            if ($this->sameExpression($checked, $this->arrayContentChecked($contentCheck, true))) {
                return self::MESSAGE_ARRAY;
            }

            $checked = $this->functionArgument($typeCheck, ['is_string']);
            if ($this->sameExpression($checked, $this->stringContentChecked($contentCheck, true))) {
                return self::MESSAGE_STRING;
            }
        }

        return null;
    }

    private function messageForOr(BooleanOr $node): ?string
    {
        foreach ($this->operandPairs($node) as [$typeCheck, $contentCheck]) {
            $checked = $this->negatedFunctionArgument($typeCheck, 'is_array');
            if ($this->sameExpression($checked, $this->arrayContentChecked($contentCheck, false))) {
                return self::MESSAGE_NOT_ARRAY;
            }

            $checked = $this->negatedFunctionArgument($typeCheck, 'is_string');
            if ($this->sameExpression($checked, $this->stringContentChecked($contentCheck, false))) {
                return self::MESSAGE_NOT_STRING;
            }
        }

        return null;
    }

    private function messageForIf(If_ $node): ?string
    {
        if ([] !== $node->elseifs || null !== $node->else) {
            return null;
        }

        $statements = $this->statementsWithoutNop($node->stmts);
        if (1 !== count($statements)) {
            return null;
        }

        $statement = $statements[0];
        $checked = $this->functionArgument($node->cond, ['is_array']);
        if ($statement instanceof Foreach_ && $this->sameExpression($checked, $this->withoutEmptyDefault($statement->expr))) {
            return self::MESSAGE_FOREACH;
        }

        $checked = $this->negatedFunctionArgument($node->cond, 'is_array');
        if ($statement instanceof Expression
            && $statement->expr instanceof Assign
            && $this->isEmptyArray($statement->expr->expr)
            && $this->sameExpression($checked, $statement->expr->var)
        ) {
            return self::MESSAGE_RESET;
        }

        return null;
    }

    /**
     * Every pair of operands across the two sides of the node, in both orders.
     *
     * In a chain like $a && $b && $c each pair is seen by exactly one node: the node that splits it.
     *
     * @return list<array{Expr, Expr}>
     */
    private function operandPairs(BooleanAnd|BooleanOr $node): array
    {
        $pairs = [];
        foreach ($this->operands($node->left, $node::class) as $left) {
            foreach ($this->operands($node->right, $node::class) as $right) {
                $pairs[] = [$left, $right];
                $pairs[] = [$right, $left];
            }
        }

        return $pairs;
    }

    /**
     * @param class-string<BinaryOp> $class
     *
     * @return list<Expr>
     */
    private function operands(Expr $expr, string $class): array
    {
        if ($expr instanceof $class) {
            return array_merge($this->operands($expr->left, $class), $this->operands($expr->right, $class));
        }

        return [$expr];
    }

    /**
     * The value an array content check is about: count($x) > 0 or [] !== $x when $populated, the empty forms otherwise.
     */
    private function arrayContentChecked(Expr $expr, bool $populated): ?Expr
    {
        $checked = $this->lengthChecked($expr, self::ARRAY_LENGTH_FUNCTIONS, $populated);
        if (null === $checked) {
            $checked = $this->comparedToEmptyArray($expr, $populated);
        }

        return $checked;
    }

    /**
     * The value a string content check is about: strlen($x) > 0 when $populated, '' === $x or strlen($x) === 0 otherwise.
     */
    private function stringContentChecked(Expr $expr, bool $populated): ?Expr
    {
        $checked = $this->lengthChecked($expr, self::STRING_LENGTH_FUNCTIONS, $populated);
        if (null === $checked && !$populated) {
            $checked = $this->comparedToEmptyString($expr);
        }

        return $checked;
    }

    /**
     * @param list<string> $functions
     */
    private function lengthChecked(Expr $expr, array $functions, bool $populated): ?Expr
    {
        if ($populated) {
            $counted = $this->functionArgument($expr, $functions);
            if (null !== $counted) {
                return $counted;
            }
        } elseif ($expr instanceof BooleanNot) {
            return $this->functionArgument($expr->expr, $functions);
        }

        if (!$expr instanceof BinaryOp) {
            return null;
        }

        $sigil = $expr->getOperatorSigil();
        $number = $expr->right;
        $counted = $this->functionArgument($expr->left, $functions);
        if (null === $counted) {
            $number = $expr->left;
            $counted = $this->functionArgument($expr->right, $functions);
            $sigil = self::MIRRORED_SIGILS[$sigil] ?? $sigil;
        }

        if (null === $counted || !$number instanceof Int_) {
            return null;
        }

        $check = $sigil . ' ' . $number->value;
        if (!array_key_exists($check, self::LENGTH_CHECKS) || $populated !== self::LENGTH_CHECKS[$check]) {
            return null;
        }

        return $counted;
    }

    private function comparedToEmptyArray(Expr $expr, bool $populated): ?Expr
    {
        if (!$expr instanceof Identical && !$expr instanceof Equal && !$expr instanceof NotIdentical && !$expr instanceof NotEqual) {
            return null;
        }

        $isEquality = $expr instanceof Identical || $expr instanceof Equal;
        if ($populated === $isEquality) {
            return null;
        }

        if ($this->isEmptyArray($expr->left)) {
            return $this->withoutEmptyDefault($expr->right);
        }

        if ($this->isEmptyArray($expr->right)) {
            return $this->withoutEmptyDefault($expr->left);
        }

        return null;
    }

    private function comparedToEmptyString(Expr $expr): ?Expr
    {
        if (!$expr instanceof Identical && !$expr instanceof Equal) {
            return null;
        }

        if ($this->isEmptyString($expr->left)) {
            return $this->withoutEmptyDefault($expr->right);
        }

        if ($this->isEmptyString($expr->right)) {
            return $this->withoutEmptyDefault($expr->left);
        }

        return null;
    }

    /**
     * The single argument of a call to one of $functions, or null for anything else.
     *
     * @param list<string> $functions
     */
    private function functionArgument(Expr $expr, array $functions): ?Expr
    {
        if (!$expr instanceof FuncCall || !$expr->name instanceof Name || $expr->isFirstClassCallable()) {
            return null;
        }

        if (!in_array($expr->name->toLowerString(), $functions, true)) {
            return null;
        }

        $args = $expr->getArgs();
        if (1 !== count($args) || $args[0]->unpack) {
            return null;
        }

        return $this->withoutEmptyDefault($args[0]->value);
    }

    /**
     * The argument of !$function($x) or false === $function($x).
     */
    private function negatedFunctionArgument(Expr $expr, string $function): ?Expr
    {
        if ($expr instanceof BooleanNot) {
            return $this->functionArgument($expr->expr, [$function]);
        }

        if (!$expr instanceof Identical) {
            return null;
        }

        if ($this->isFalse($expr->left)) {
            return $this->functionArgument($expr->right, [$function]);
        }

        if ($this->isFalse($expr->right)) {
            return $this->functionArgument($expr->left, [$function]);
        }

        return null;
    }

    /**
     * Reads $x ?? null, $x ?? [] and $x ?? '' as $x, so is_array($x ?? null) matches count($x).
     */
    private function withoutEmptyDefault(Expr $expr): Expr
    {
        if ($expr instanceof Coalesce
            && ($this->isNull($expr->right) || $this->isEmptyArray($expr->right) || $this->isEmptyString($expr->right))
        ) {
            return $expr->left;
        }

        return $expr;
    }

    /**
     * @param Stmt[] $statements
     *
     * @return list<Stmt>
     */
    private function statementsWithoutNop(array $statements): array
    {
        $kept = [];
        foreach ($statements as $statement) {
            if (!$statement instanceof Nop) {
                $kept[] = $statement;
            }
        }

        return $kept;
    }

    private function isEmptyArray(Expr $expr): bool
    {
        return $expr instanceof Array_ && [] === $expr->items;
    }

    private function isEmptyString(Expr $expr): bool
    {
        return $expr instanceof String_ && '' === $expr->value;
    }

    private function isNull(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && 'null' === $expr->name->toLowerString();
    }

    private function isFalse(Expr $expr): bool
    {
        return $expr instanceof ConstFetch && 'false' === $expr->name->toLowerString();
    }

    private function sameExpression(?Expr $left, ?Expr $right): bool
    {
        if (null === $left || null === $right) {
            return false;
        }

        $dumper = new NodeDumper([
            'dumpComments' => false,
            'dumpPositions' => false,
            'dumpOtherAttributes' => false,
        ]);

        return $dumper->dump($left) === $dumper->dump($right);
    }
}
