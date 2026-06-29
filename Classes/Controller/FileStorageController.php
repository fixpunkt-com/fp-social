<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Controller;

use Fixpunkt\FpFileprotector\Domain\Repository\FileStorageRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
class FileStorageController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly FileStorageRepository $fileStorageRepository,
    ) {}

    /**
     * Provides the file storage edit form.
     *
     * @param string $id
     * @param int $fileStorageUid
     * @return ResponseInterface
     */
    public function editAction(string $id, int $fileStorageUid): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $moduleTemplate->assignMultiple([
            'fileStorage' => $this->fileStorageRepository->findByIdentifier($fileStorageUid),
            'id' => $id,
        ]);
        return $moduleTemplate->renderResponse('FileStorage/Edit');
    }

    /**
     * Updates settings of a file storage.
     *
     * @param int $id
     * @param int $fileStorageUid
     * @param bool $protected
     * @param bool $protectedByDefault
     * @return ResponseInterface
     */
    public function updateAction(int $fileStorageUid, bool $protected, bool $protectedByDefault): ResponseInterface
    {
        $fileStorage = $this->fileStorageRepository->findByIdentifier($fileStorageUid);

        $changed = $fileStorage->isProtected() != $protected || $fileStorage->isProtectedByDefault() != $protectedByDefault;
        if ($changed) {
            $fileStorage->setProtected($protected);
            $fileStorage->setProtectedByDefault($protectedByDefault);
            $this->fileStorageRepository->update($fileStorage);
            $this->addFlashMessage(LocalizationUtility::translate('sys_file_storage.flashmessages.updated', 'FpFileprotector'));
        }

        $fileStorage->modifyHtaccess();

        $id = $this->request->getQueryParams()['id'] ?? '';
        return $this->redirect('show', 'Folder', null, ['id' => $id, 'refreshFolderTree' => $changed]);
    }

    /**
     * Updates the .htaccess file.
     *
     * @param string $id
     * @param int $fileStorageUid
     * @return ResponseInterface
     */
    public function htaccessAction(string $id, int $fileStorageUid): ResponseInterface
    {
        $this->fileStorageRepository->findByIdentifier($fileStorageUid)->modifyHtaccess();

        $this->addFlashMessage(LocalizationUtility::translate('sys_file_storage.flashmessages.htaccess', 'FpFileprotector'));
        return $this->redirect('show', 'Folder', null, ['id' => $id]);
    }
}
