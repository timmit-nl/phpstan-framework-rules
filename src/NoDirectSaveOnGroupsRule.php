<?php

/**
 * PHPStan rule that keeps every write to a group on the diffAndSave() route.
 *
 * Registered in rules.neon.
 */

declare(strict_types=1);

namespace Timmit\Phpstan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

/**
 * Reports calls to Groups::save() made from outside diffAndSave().
 *
 * A class counts when its own short name is Groups, or an ancestor's is, and that class has
 * diffAndSave(). Gull\Framework\Groups and TiMMiT\Mosquito\Groups match the same way, and so do
 * application subclasses. A Groups class without diffAndSave() is left alone.
 *
 * Covers both `$groups->save()` / `$this->save()` and `parent::save()` in Groups subclasses.
 *
 * @implements Rule<CallLike>
 */
class NoDirectSaveOnGroupsRule implements Rule
{
    public function getNodeType(): string
    {
        return CallLike::class;
    }

    /**
     * @param CallLike $node
     * @param Scope $scope
     *
     * @return (string|\PHPStan\Rules\RuleError)[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof MethodCall && !$node instanceof StaticCall) {
            return [];
        }

        if (!$node->name instanceof Node\Identifier || 'save' !== $node->name->toLowerString()) {
            return [];
        }

        if ($node instanceof MethodCall) {
            if (!$this->receiverIsGroupsWithDiffAndSave($scope->getType($node->var))) {
                return [];
            }
        } else {
            /* parent::save() in a Groups subclass bypasses diffAndSave() the same way. */
            if (!$node->class instanceof Node\Name || 'parent' !== $node->class->toLowerString()) {
                return [];
            }

            $classReflection = $scope->getClassReflection();
            if (null === $classReflection || !$this->classIsGroupsWithDiffAndSave($classReflection)) {
                return [];
            }
        }

        /* diffAndSave() is the one method that writes the record itself. */
        $classReflection = $scope->getClassReflection();
        $functionReflection = $scope->getFunction();
        if (
            null !== $classReflection
            && null !== $functionReflection
            && $this->classIsGroupsWithDiffAndSave($classReflection)
            && 'diffandsave' === strtolower($functionReflection->getName())
        ) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                'Save a group with diffAndSave() instead of save(). A direct save() skips the work diffAndSave() does before writing the row.'
            )
                ->identifier('timmit.groupsDirectSave')
                ->build(),
        ];
    }

    private function receiverIsGroupsWithDiffAndSave(Type $type): bool
    {
        foreach ($type->getObjectClassReflections() as $classReflection) {
            if ($this->classIsGroupsWithDiffAndSave($classReflection)) {
                return true;
            }
        }

        return false;
    }

    /**
     * True when this class or an ancestor is named Groups and has diffAndSave().
     */
    private function classIsGroupsWithDiffAndSave(ClassReflection $classReflection): bool
    {
        $current = $classReflection;
        while ($current instanceof ClassReflection) {
            if ('Groups' === $this->shortNameOf($current) && $current->hasMethod('diffAndSave')) {
                return true;
            }

            $parent = $current->getParentClass();
            if (!$parent instanceof ClassReflection) {
                return false;
            }

            $current = $parent;
        }

        return false;
    }

    private function shortNameOf(ClassReflection $classReflection): string
    {
        $name = $classReflection->getName();
        $separator = strrpos($name, '\\');
        if (false === $separator) {
            return $name;
        }

        return substr($name, $separator + 1);
    }
}
