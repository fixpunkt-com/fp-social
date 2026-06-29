<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InArrayViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('array', 'array', 'Das zu durchsuchende Array.', true);
        $this->registerArgument('value', 'mixed', 'Das zu findende Objekt.', true);
    }

    public function render(): bool
    {
        if (!$this -> arguments['array'] || !is_array($this -> arguments['array'])) {
            return false;
        }
        return in_array($this -> arguments['value'], $this -> arguments['array']);
    }
}
