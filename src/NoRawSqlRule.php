<?php

/**
 * PHPStan rule: discourage new hand-written SQL in application code.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace TiMMiT\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Flags string literals that look like SQL DML in application code.
 *
 * @implements Rule<Node>
 */
class NoRawSqlRule implements Rule
{
    private const SQL_PATTERN = '/^\s*(SELECT|INSERT|UPDATE|DELETE)\s/i';

    public function getNodeType(): string
    {
        return Node::class;
    }

    /**
     * @param Node $node
     * @param Scope $scope
     *
     * @return (string|\PHPStan\Rules\RuleError)[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($node instanceof String_) {
            return $this->checkSqlFragment($node->value);
        }

        if ($node instanceof Encapsed) {
            return $this->checkSqlFragment($this->assembleSqlText($node));
        }

        if ($node instanceof Concat) {
            return $this->checkConcat($node);
        }

        return [];
    }

    /**
     * Reports a concatenation only when the assembled text starts like SQL and no fragment already does.
     *
     * A lone 'SELECT' misses SQL_PATTERN because the keyword is not followed by whitespace.
     * Joined with ' * FROM users', or an interpolation such as "SELECT{$columns}", it does.
     * Nested concatenations report on the inner node that first forms the SQL, so the outer one stays quiet.
     *
     * @return (string|\PHPStan\Rules\RuleError)[]
     */
    private function checkConcat(Concat $node): array
    {
        if (!$this->looksLikeSql($this->assembleSqlText($node))) {
            return [];
        }

        if ($this->containsSqlFragment($node)) {
            return [];
        }

        if ($node->left instanceof Concat && $this->looksLikeSql($this->assembleSqlText($node->left))) {
            return [];
        }

        return $this->sqlError();
    }

    /**
     * @return (string|\PHPStan\Rules\RuleError)[]
     */
    private function checkSqlFragment(string $value): array
    {
        if (!$this->looksLikeSql($value)) {
            return [];
        }

        return $this->sqlError();
    }

    private function looksLikeSql(string $value): bool
    {
        return 1 === preg_match(self::SQL_PATTERN, $value);
    }

    /**
     * True when a child string already matches SQL_PATTERN on its own, so that node reports the error.
     */
    private function containsSqlFragment(Node $node): bool
    {
        if ($node instanceof String_ || $node instanceof EncapsedStringPart) {
            return $this->looksLikeSql($node->value);
        }

        if ($node instanceof Encapsed) {
            return $this->looksLikeSql($this->assembleSqlText($node));
        }

        if ($node instanceof Concat) {
            return $this->containsSqlFragment($node->left) || $this->containsSqlFragment($node->right);
        }

        return false;
    }

    /**
     * Joins string fragments and treats interpolations or other expressions as whitespace.
     */
    private function assembleSqlText(Node $node): string
    {
        if ($node instanceof String_ || $node instanceof EncapsedStringPart) {
            return $node->value;
        }

        if ($node instanceof Encapsed) {
            $text = '';
            foreach ($node->parts as $part) {
                if ($part instanceof Node) {
                    $text .= $this->assembleSqlText($part);
                }
            }

            return $text;
        }

        if ($node instanceof Concat) {
            return $this->assembleSqlText($node->left) . $this->assembleSqlText($node->right);
        }

        return ' ';
    }

    /**
     * @return (string|\PHPStan\Rules\RuleError)[]
     */
    private function sqlError(): array
    {
        return [
            RuleErrorBuilder::message(
                'Avoid raw SQL in application code; prefer getIdFromCollection(), Collection, or other aItem helpers.'
            )
                ->identifier('timmit.noRawSql')
                ->build(),
        ];
    }
}
