<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__)
    ->exclude(['.build'])
    // ext_emconf.php is a plain array config read by the Extension Manager and,
    // by TYPO3 convention, is exempt from the declare(strict_types=1) requirement.
    ->notPath('ext_emconf.php')
;

// Enforce declare(strict_types=1) in every PHP file (TYPO3 general requirement;
// not part of the default rule set because it is a "risky" fixer).
$config->addRules([
    'declare_strict_types' => true,
]);

return $config;
