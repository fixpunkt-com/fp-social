<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\ViewHelpers\Arrays;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MergeViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('array1', 'array', 'Das erste Array.', true);
        $this->registerArgument('array2', 'array', 'Das zweite Array.', true);
    }

    public function render(): array
    {
        if (!$this -> arguments['array1'] || !is_array($this -> arguments['array1'])) {
            if (!$this -> arguments['array2'] || !is_array($this -> arguments['array2'])) {
                return [];
            }
            return $this -> arguments['array2'];

        }
        if (!$this -> arguments['array2'] || !is_array($this -> arguments['array2'])) {
            return $this -> arguments['array1'];
        }

        return array_merge($this -> arguments['array1'], $this -> arguments['array2']);
    }
}
