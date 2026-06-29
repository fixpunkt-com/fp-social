<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ForColumnsViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('records', 'array', 'Records to sort.', true);
        $this->registerArgument('columns', 'int', 'Amount of columns.', true);
        $this->registerArgument('as', 'string', 'The name of the iteration variable', true);
    }

    /**
     * Gibt ein Bild aus einem Social Media Profil aus und lädt es dazu ggf. herunter.
     * @return string
     */
    public function render(): string
    {
        // sort records into columns
        $recordsByColumns = [];
        for ($i = 0; $i < count($this -> arguments['records']); $i++) {
            $recordsByColumns[$i % $this -> arguments['columns']][] = $this -> arguments['records'][$i];
        }

        // output the columns
        $globalVariableProvider = $this->renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider();
        $this->renderingContext->setVariableProvider(new ScopedVariableProvider($globalVariableProvider, $localVariableProvider));
        $output = '';
        foreach ($recordsByColumns as $recordsByColumn) {
            $localVariableProvider->add($this->arguments['as'], $recordsByColumn);
            $output .= $this->renderChildren();
        }
        $this->renderingContext->setVariableProvider($globalVariableProvider);
        return $output;
    }
}
