# Entwicklung / Dev-Tooling

Diese Extension wird über ein **eigenständiges DDEV-Projekt** entwickelt. Das
Tooling (Coding Standards + statische Analyse) liegt isoliert in
* `.build/` (gegen TYPO3 14.3)
* `.build-v13/` (gegen TYPO3 13.4)
* `.build-v12/` (gegen TYPO3 12.4)

damit die `composer.json` der Extension selbst sauber bleibt. Eingecheckt sind nur die
Tooling-Manifeste; `vendor/`, Lockfiles und generierte Assets sind ignoriert.

## Einrichtung

```bash
ddev start
ddev exec "cd .build && composer install"
```

> Das Tooling zieht TYPO3-Core, PHPStan und die Runtime-Deps zur
> Typauflösung.

## Coding Standards (php-cs-fixer)

Grundlage: `typo3/coding-standards` (PSR-12 / PER-CS) inkl. erzwungenem
`declare(strict_types=1)`. Konfiguration: `.php-cs-fixer.dist.php`, `.editorconfig`.

```bash
# Nur prüfen – ändert nichts:
ddev exec ".build/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff --using-cache=no"

# Automatisch korrigieren:
ddev exec ".build/vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --using-cache=no"
```

## Statische Analyse (PHPStan)

Level 5 über `Classes/`, TYPO3-Regeln via `saschaegerer/phpstan-typo3`.

```bash
# gegen TYPO3 14.3 (Standard-Toolchain, phpstan.neon wird automatisch gefunden):
ddev exec ".build/vendor/bin/phpstan analyse"

# gegen TYPO3 13.4 (niedrigste unterstützte Version):
ddev exec "cd .build-v13 && composer install"   # einmalig
ddev exec ".build-v1/vendor/bin/phpstan analyse"

# gegen TYPO3 12.4 (niedrigste unterstützte Version):
ddev exec "cd .build-v12 && composer install"   # einmalig
ddev exec ".build-v12/vendor/bin/phpstan analyse -c .build-v12/phpstan.neon"
```

## Continuous Integration

`.github/workflows/ci.yml` fährt bei Push/Pull-Request PHPStan (Matrix
TYPO3 12.4 + 13.4 + 14.3) sowie php-cs-fixer und nutzt dafür dieselben `.build`-/ `.build-12`-/
`.build-v13`-Toolchains.

## Tooling aktualisieren

```bash
ddev exec "cd .build && composer update"
ddev exec "cd .build-v12 && composer update"
ddev exec "cd .build-v13 && composer update"
```
