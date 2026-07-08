<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\ViewHelpers\Format;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class EmojiViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('text', 'string', 'Der Text in dem die Emojis ersetzt werden sollen.', true);
    }

    public function render(): string
    {
        return \Fixpunkt\FpSocial\Utilities\EmojiUtility::decode($this -> arguments['text']);
    }
}
