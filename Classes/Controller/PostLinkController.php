<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Model\PostLink;
use Fixpunkt\FpSocial\Domain\Repository\PostLinkRepository;
use Fixpunkt\FpSocial\Property\TypeConverters\HiddenObjectConverter;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

class PostLinkController extends ActionController
{
    /** @var PostLinkRepository */
    protected PostLinkRepository $postLinkRepository;

    /**
     * PostLinkController constructor.
     * @param PostLinkRepository $postLinkRepository
     */
    public function __construct(PostLinkRepository $postLinkRepository)
    {
        $this -> postLinkRepository = $postLinkRepository;
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeUpdateAction(): void
    {
        $this->arguments->getArgument('postLink')->getPropertyMappingConfiguration()->setTypeConverter(
            GeneralUtility::makeInstance(HiddenObjectConverter::class)
        );
    }
    /**
     * Updates a single postlink.
     * @param PostLink $postLink
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function updateAction(PostLink $postLink): ResponseInterface
    {
        $this -> postLinkRepository -> update($postLink);
        $this->addFlashMessage('Der Post wurde aktualisiert.');
        return $this -> redirect('show', 'Account', null, ['account' => $postLink -> getAccount()]);
    }
}
