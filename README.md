# timmit/phpstan-framework-rules

PHPStan rules shared by applications on a aItem based framework, plus [phpstan-strict-rules](https://github.com/phpstan/phpstan-strict-rules).

The rules match method names and the short class name (`Functions`, `Groups`). They do not mention  `Name\Space\Groups::save()` is reported only when that class, or an ancestor with the same short name, has `diffAndSave()`.

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
