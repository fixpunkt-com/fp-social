<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Controller;

use Fixpunkt\FpFileprotector\Domain\Model\Protection;
use Fixpunkt\FpFileprotector\Domain\Repository\FolderRepository;
use Fixpunkt\FpFileprotector\Domain\Repository\FrontendUserGroupRepository;
use Fixpunkt\FpFileprotector\Domain\Repository\FrontendUserRepository;
use Fixpunkt\FpFileprotector\Domain\Repository\ProtectionRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
class ProtectionController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly ProtectionRepository $protectionRepository,
        protected readonly FrontendUserGroupRepository $userGroupRepository,
        protected readonly FrontendUserRepository $userRepository,
        protected readonly FolderRepository $folderRepository,
    ) {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->userGroupRepository->setDefaultQuerySettings($querySettings);
        $this->userRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * Provides the folder protection creation form.
     *
     * @param string $combinedIdentifier
     * @return ResponseInterface
     */
    public function newAction(string $combinedIdentifier): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $moduleTemplate->assignMultiple([
            'protection' => GeneralUtility::makeInstance(Protection::class),
            'folder' => $this->folderRepository->findOneByCombinedIdentifier($combinedIdentifier),
            'userGroups' => $this->userGroupRepository->findAll(),
            'users' => $this->userRepository->findAll(),
        ]);

        return $moduleTemplate->renderResponse('Protection/New');
    }

    /**
     * Creates a new folder protection.
     *
     * @param Protection $protection
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     */
    public function createAction(Protection $protection): ResponseInterface
    {
        $this->protectionRepository->add($protection);
        $this->addFlashMessage(LocalizationUtility::translate(
            'tx_fpfileprotector_domain_model_protection.flashmessages.created',
            'FpFileprotector'
        ));
        return $this->redirect('show', 'Folder', null, ['id' => $protection->getFolderObject()->getCombinedIdentifier(), 'refreshFolderTree' => true]);
    }

    /**
     * Provides the folder protection edit form.
     *
     * @param Protection $protection
     * @return ResponseInterface
     */
    public function editAction(Protection $protection): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $moduleTemplate->assignMultiple([
            'protection' => $protection,
            'folder' => $protection->getFolderObject(),
            'userGroups' => $this->userGroupRepository->findAll(),
            'users' => $this->userRepository->findAll(),
        ]);

        return $moduleTemplate->renderResponse('Protection/Edit');
    }

    /**
     * @param Protection $protection
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(Protection $protection): ResponseInterface
    {
        $this->protectionRepository->update($protection);
        $this->addFlashMessage(LocalizationUtility::translate(
            'tx_fpfileprotector_domain_model_protection.flashmessages.updated',
            'FpFileprotector'
        ));

        return $this->redirect('show', 'Folder', null, ['id' => $protection->getFolderObject()->getCombinedIdentifier(), 'refreshFolderTree' => true]);
    }

    /**
     * Removes folder protection.
     *
     * @param Protection $protection
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     */
    public function deleteAction(Protection $protection): ResponseInterface
    {
        $this->protectionRepository->remove($protection);
        $this->addFlashMessage(LocalizationUtility::translate(
            'tx_fpfileprotector_domain_model_protection.flashmessages.deleted',
            'FpFileprotector'
        ));
        return $this->redirect('show', 'Folder', null, ['id' => $protection->getFolderObject()->getCombinedIdentifier(), 'refreshFolderTree' => true]);
    }
}