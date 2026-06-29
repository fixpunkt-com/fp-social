<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class AbstractController extends ActionController
{
    /** @var ModuleTemplateFactory  */
    protected ModuleTemplateFactory $moduleTemplateFactory;

    /**
     * @param ModuleTemplateFactory $moduleTemplateFactory
     */
    public function __construct(ModuleTemplateFactory $moduleTemplateFactory)
    {
        $this -> moduleTemplateFactory = $moduleTemplateFactory;
    }

    /**
     * Übergibt Informationen zu Controller und Action an den View.
     */
    protected function initializeView()
    {
        if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
            $this -> view -> assign(
                'context',
                [
                    'controller' => $this -> request -> getControllerName(),
                    'action' => $this -> request -> getControllerActionName(),
                ]
            );
        }
    }
}
