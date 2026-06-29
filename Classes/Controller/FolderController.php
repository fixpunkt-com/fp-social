<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Controller;

use Fixpunkt\FpFileprotector\Domain\Repository\FolderRepository;
use Fixpunkt\FpFileprotector\Resource\Folder;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Buttons\DropDown\DropDownItem;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
class FolderController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly PageRenderer $pageRenderer,
        protected readonly IconFactory $iconFactory,
        protected readonly FolderRepository $folderRepository,
        protected readonly StorageRepository $storageRepository,
    ) {}

    /**
     * Shows information for a single folder.
     *
     * @param string $id
     * @return ResponseInterface
     */
    public function showAction(string $id = "", bool $refreshFolderTree = false): ResponseInterface
    {
        // modify id or redirect to first folder
        $id = $this->modifyId($id);
        if (!$id) {
            return $this->redirectToFirstStorage();
        }
        /** @var Folder $folder */
        $folder = $this->folderRepository->findOneByCombinedIdentifier($id);

        // refresh folder after change
        if ($refreshFolderTree) {
            $this->pageRenderer->loadJavaScriptModule('@fixpunkt/fp-fileprotector/reload-folder-tree.js');
        }

        // generate template
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->initializeDocHeader($moduleTemplate, $folder);
        $this->statusCheck($folder);
        $moduleTemplate->assign('folder', $folder);
        return $moduleTemplate->renderResponse('Folder/Show');
    }

    protected function statusCheck(Folder $folder): void
    {
        // show information if the storage is NOT protected
        if (!$folder->getStorage()->isProtected()) {
            if ($folder->isProtected()) {
                $this->addFlashMessage(
                    LocalizationUtility::translate('folder.show.storage_not_protected_with_rule', 'FpFileprotector'),
                    LocalizationUtility::translate('folder.show.storage_not_protected', 'FpFileprotector'),
                    ContextualFeedbackSeverity::ERROR
                );
            } else {
                $this->addFlashMessage(
                    LocalizationUtility::translate('folder.show.storage_not_protected_no_rule', 'FpFileprotector'),
                    LocalizationUtility::translate('folder.show.storage_not_protected', 'FpFileprotector'),
                    ContextualFeedbackSeverity::INFO
                );
            }
        }

        // show warning if the storage is protected, but .htaccess malfunctioning
        // todo
    }

    /**
     * Initialized the docheader.
     *
     * @param ModuleTemplate $moduleTemplate
     * @param Folder $folder
     * @return void
     */
    protected function initializeDocHeader(ModuleTemplate $moduleTemplate, Folder $folder): void
    {
        $moduleTemplate->getDocHeaderComponent()->setMetaInformationForResource($folder);

        $editStorageUri = $this->uriBuilder->reset()
            ->uriFor('edit', ['fileStorageUid' => $folder->getStorage()->getUid()], 'FileStorage');
        $htaccessUri = $this->uriBuilder->reset()
            ->uriFor('htaccess', ['fileStorageUid' => $folder->getStorage()->getUid()], 'FileStorage');

        // add buttons
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $dropdownButton = $buttonBar->makeDropDownButton()
            ->setShowLabelText(true);

        if ($folder->getStorage()->isProtected()) {
            $dropdownButton
                ->setIcon($this->iconFactory->getIcon('tx-fpfileprotector-folder-protected'))
                ->setLabel(LocalizationUtility::translate('module.storage_is_protected', 'FpFileprotector'));
        } else {
            $dropdownButton
                ->setIcon($this->iconFactory->getIcon('tx-fpfileprotector-folder-public'))
                ->setLabel(LocalizationUtility::translate('module.storage_is_unprotected', 'FpFileprotector'));
        }
        $dropdownButton
        ->addItem(
            GeneralUtility::makeInstance(DropDownItem::class)
                ->setLabel(LocalizationUtility::translate('module.dropdown.storage_settings', 'FpFileprotector'))
                ->setHref($editStorageUri)
        )
        ->addItem(
            GeneralUtility::makeInstance(DropDownItem::class)
                ->setLabel(LocalizationUtility::translate('module.dropdown.htaccess_update', 'FpFileprotector'))
                ->setHref($htaccessUri)
        );
        $buttonBar->addButton($dropdownButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * Creates a redirect Response to redirect to the first folder.
     *
     * @return ResponseInterface
     */
    protected function redirectToFirstStorage(): ResponseInterface
    {
        $storages = $this->storageRepository->findAll();
        $firstStorage = reset($storages);
        return $this->redirect('show', null, null, ['id' => $firstStorage->getUid() . ':/']);
    }

    /**
     * Checks if a folder id is given.
     * If not get one from module data.
     *
     * @param string $id
     * @return string
     */
    protected function modifyId(string $id): string
    {
        $moduleData = $this->request->getAttribute('moduleData');
        if ($id) {
            $moduleData->set('id', $id);
            $GLOBALS['BE_USER']->pushModuleData($moduleData->getModuleIdentifier(), $moduleData->toArray());
            return $id;
        } else {
            return $moduleData->get('id', '');
        }
    }
}
