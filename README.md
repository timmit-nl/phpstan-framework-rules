# timmit/phpstan-framework-rules

PHPStan rules shared by applications on an aItem based framework, plus [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The rules match method names and the short class name (`Functions`, `Groups`, `aItem`). They do not use the namespace. `Groups::save()` is reported only when that class, or an ancestor with the same short name, has `diffAndSave()`.

## Install

```bash
composer require --dev timmit/phpstan-framework-rules
```

`rules.neon` includes `phpstan-strict-rules`. One include is enough:

```neon
includes:
    - vendor/timmit/phpstan-framework-rules/rules.neon
```

With `phpstan/extension-installer` the include is picked up from `extra.phpstan.includes`. Do not also include `vendor/phpstan/phpstan-strict-rules/rules.neon`; the rules would be registered twice.

`NoDirectSaveOnGroupsRule` stays quiet for a `Groups` class that has no `diffAndSave()`. `Users` and `Login` are separate classes and are not covered by this rule.

## Rules

| Rule | Reports |
| --- | --- |
| `DateTimeFormatIntComparisonRule` | A numeric `DateTime::format()` part compared to a string |
| `NoAItemInSignatureRule` | An aItem type on a parameter or property. Pass the id instead |
| `NoClosureRule` | A closure (`function () {}`). A named method and a first-class callable stay allowed |
| `NoCountOnAItemBaseRule` | `count()` or `Functions::isPopulatedArray()` on `::$array[$id]['base']`. Use `isLoaded($id)` |
| `NoDateTimeModifyRule` | `DateTime::modify()`. Use `add()` / `sub()` or `Functions::modifyDateTimeFromInterval()` |
| `NoDirectSaveOnGroupsRule` | `save()` on a `Groups` class from outside `diffAndSave()` |
| `NoEmptyCoalesceStringComparisonRule` | Comparing `($expr ?? '')` to `''`. Use `Functions::isPopulatedString($expr ?? null)` |
| `NoEmptyStringInequalityRule` | `!== ''` on a string. Use `Functions::isPopulatedString()` |
| `NoEmptyStringResetAfterIsPopulatedStringRule` | Assigning `''` inside `if (!Functions::isPopulatedString($var))` |
| `NoGetBaseFieldOfJugglingRule` | A cast or `??` on `getBaseFieldOf()`, and `getBaseFieldOf(..., 'group_id')` instead of `getGroupIdOf()` |
| `NoImmediateCallOnNewRule` | A method call or property read directly on `new` |
| `NoIssetWithIsPopulatedArrayRule` | `!isset($x)` combined with `!Functions::isPopulatedArray($x)` |
| `NoLooseDateFunctionsRule` | `gmdate()` and `strtotime()` |
| `NoLooseDateTimeConstructorRule` | `new DateTime('...')`. Use `createFromFormat()` or a `Functions` helper |
| `NoRawSqlRule` | A string literal that looks like SQL DML |
| `NoShorthandRule` | A shorthand operator other than `??` |
| `YodaComparisonRule` | `===` or `!==` against a scalar constant that is not written Yoda-style |
