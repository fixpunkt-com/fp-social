<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
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
     * Erzeugt den Backend-View. Jede Action muss über dieses ModuleTemplate
     * rendern, sonst wird das Modul ohne die Backend-Shell (und damit ohne
     * deren CSS) ausgeliefert.
     *
     * Übergibt außerdem Informationen zu Controller und Action an den View,
     * damit die Navigation den aktiven Eintrag kennt.
     */
    protected function createModuleView(): ModuleTemplate
    {
        $view = $this -> moduleTemplateFactory -> create($this -> request);
        $view -> assign(
            'context',
            [
                'controller' => $this -> request -> getControllerName(),
                'action' => $this -> request -> getControllerActionName(),
            ]
        );
        return $view;
    }
}
