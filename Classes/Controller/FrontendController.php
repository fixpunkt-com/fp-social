<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Controller;

use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
use Fixpunkt\FpSocial\Domain\Repository\AccountRepository;
use Fixpunkt\FpSocial\Domain\Repository\HashtagRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostLinkRepository;
use Fixpunkt\FpSocial\Domain\Repository\PostRepository;
use Fixpunkt\FpSocial\Events\RecordCollectionEvent;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3Fluid\Fluid\View\AbstractTemplateView;

class FrontendController extends ActionController
{
    /**
     * @param PostRepository $postRepository
     * @param PostLinkRepository $postLinkRepository
     * @param AccountRepository $accountRepository
     * @param HashtagRepository $hashtagRepository
     * @param RecordCollectionEvent $recordCollectionEvent
     * @param ConnectionPool $connectionPool
     */
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly PostLinkRepository $postLinkRepository,
        protected readonly AccountRepository $accountRepository,
        protected readonly HashtagRepository $hashtagRepository,
        protected readonly RecordCollectionEvent $recordCollectionEvent,
        protected readonly ConnectionPool $connectionPool
    ) {}

    /**
     * Shows a single post in frontend.
     */
    public function singleAction(): ResponseInterface
    {
        $contentObjectData = $this -> getContentObjectData();

        // Post Link einlesen
        $recordIdentifier = $contentObjectData['tx_fpsocial_post'] ?? 0;
        if (!$recordIdentifier) {
            return $this -> htmlResponse('');
        }
        $record = $this -> recordCollectionEvent -> findByIdentifier($recordIdentifier);
        if (!$record) {
            return $this -> htmlResponse('');
        }

        $this -> view -> assignMultiple([
            'record' => $record,
            'data' => $contentObjectData,
        ]);
        return $this -> htmlResponse();
    }

    /**
     * @return ResponseInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function wallAction(): ResponseInterface
    {
        $contentObjectData = $this -> getContentObjectData();

        // read data
        $preselectedRecordsIdentifiers = explode(',', $contentObjectData['tx_fpsocial_records'] ?? '');
        $collectionIdentifiers = explode(',', $contentObjectData['tx_fpsocial_accounts'] ?? '');
        $hashtags = $this -> hashtagRepository -> findByUids(explode(',', $contentObjectData['tx_fpsocial_hashtags'] ?? '')) -> toArray();
        $rows = $contentObjectData['tx_fpsocial_wall_rows'] ?? 1;
        $columns = $contentObjectData['tx_fpsocial_wall_columns'] ?? 1;

        // get records
        $preselectedRecords = $this -> recordCollectionEvent -> findByIdentifiers($preselectedRecordsIdentifiers);
        $recordsFromCollections = $this -> getRecordsFromCollections($collectionIdentifiers, $hashtags, $preselectedRecordsIdentifiers, $rows * $columns - count($preselectedRecords));

        // output
        if (!$preselectedRecords && !$recordsFromCollections) {
            return $this -> htmlResponse('');
        }

        // merge records
        $allRecords = [];
        foreach ($preselectedRecords as $preselectedRecord) {
            $allRecords[] = ['record' => $preselectedRecord, 'source' => 'preselected'];
        }
        foreach ($recordsFromCollections as $recordFromCollection) {
            $allRecords[] = ['record' => $recordFromCollection, 'source' => 'collection'];
        }
        usort($allRecords, function (array $recordAWrapper, array $recordBWrapper): int {
            /** @var RecordInterface $recordA */
            $recordA = $recordAWrapper['record'];
            /** @var RecordInterface $recordB */
            $recordB = $recordBWrapper['record'];
            return $recordA -> getUpdatedTime() < $recordB -> getUpdatedTime() ? 1 : 0;
        });

        $this -> view -> assignMultiple([
            'records' => $allRecords,
            'columns' => $columns,
            'data' => $contentObjectData,
            //'cUid' => $this -> request -> getAttribute('currentContentObject') -> data['uid']
        ]);
        return $this -> htmlResponse();
    }

    /**
     * @param int $contentObjectUid
     * @param array $referenceRecords
     * @param int $amount
     * @return ResponseInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxLoadOlderAction(int $contentObjectUid, array $referenceRecords, int $amount): ResponseInterface
    {
        $contentObjectData = $this -> getContentObjectData($contentObjectUid);

        // modify reference records
        $oldestReferenceRecords = [];
        foreach ($referenceRecords as $recordIdentifier => $data) {
            $oldestReferenceRecords[$recordIdentifier] = $data['oldest']['identifier'];
        }

        // read flexform data
        $preselectedRecordsIdentifiers = explode(',', $contentObjectData['tx_fpsocial_records'] ?? '');
        $collectionIdentifiers = explode(',', $contentObjectData['tx_fpsocial_accounts'] ?? '');
        $hashtags = $this -> hashtagRepository -> findByUids(explode(',', $contentObjectData['tx_fpsocial_hashtags'] ?? '')) -> toArray();

        // get records
        $records = $this -> getRecordsFromCollections($collectionIdentifiers, $hashtags, $preselectedRecordsIdentifiers, $amount, $oldestReferenceRecords);

        return $this -> ajaxRenderAndReturn($contentObjectUid, $records);
    }

    /**
     * @param int $contentObjectUid
     * @param array $referenceRecords
     * @param int $amount
     * @return ResponseInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function ajaxLoadNewerAction(int $contentObjectUid, array $referenceRecords, int $amount): ResponseInterface
    {
        $contentObjectData = $this -> getContentObjectData($contentObjectUid);

        // modify reference records
        $newestReferenceRecords = [];
        foreach ($referenceRecords as $recordIdentifier => $data) {
            $newestReferenceRecords[$recordIdentifier] = $data['newest']['identifier'];
        }

        // read flexform data
        $preselectedRecordsIdentifiers = explode(',', $contentObjectData['tx_fpsocial_records'] ?? '');
        $collectionIdentifiers = explode(',', $contentObjectData['tx_fpsocial_accounts'] ?? '');
        $hashtags = $this -> hashtagRepository -> findByUids(explode(',', $contentObjectData['tx_fpsocial_hashtags'] ?? '')) -> toArray();

        // get records
        $records = $this -> getRecordsFromCollections(
            $collectionIdentifiers,
            $hashtags,
            $preselectedRecordsIdentifiers,
            $amount,
            $newestReferenceRecords,
            QueryInterface::ORDER_ASCENDING
        );

        return $this -> ajaxRenderAndReturn($contentObjectUid, $records);

    }

    protected function ajaxRenderAndReturn(int $contentObjectUid, array $records): ResponseInterface
    {
        $contentObjectData = $this -> getContentObjectData($contentObjectUid);

        // render posts
        $rendered = [];
        /** @var RecordInterface $record */
        foreach ($records as $record) {
            $rendered[] = $this -> renderRecord($record, $contentObjectData);
        }

        return $this -> jsonResponse(json_encode([
            'records' => $rendered,
            'amount' => count($rendered),
        ]));
    }

    /**
     * Renders a single record (the "Post/Show" partial) to a string.
     *
     * TYPO3 v13.3+/v14 no longer expose the Fluid-specific renderPartial() on the Extbase
     * view, so we render a dedicated template through the generic ViewFactory there. On v12
     * (no ViewFactoryInterface) we keep using the view's renderPartial().
     */
    protected function renderRecord(RecordInterface $record, array $contentObjectData): string
    {
        $variables = [
            'post' => $record,
            'account' => $record -> getAccount(),
            'source' => 'collection',
            'data' => $contentObjectData,
        ];

        if (interface_exists(ViewFactoryInterface::class)) {
            $frameworkConfiguration = $this -> configurationManager -> getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
            );
            $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
            $view = $viewFactory -> create(new ViewFactoryData(
                templateRootPaths: $frameworkConfiguration['view']['templateRootPaths'] ?? [],
                partialRootPaths: $frameworkConfiguration['view']['partialRootPaths'] ?? [],
                layoutRootPaths: $frameworkConfiguration['view']['layoutRootPaths'] ?? [],
                request: $this -> request,
            ));
            $view -> assignMultiple($variables);
            return $view -> render('Ajax/Post');
        }

        // TYPO3 v12 fallback. AbstractTemplateView::renderPartial() accepts a null $sectionName
        // (unlike the imprecise ViewInterface PHPDoc), which is required so the whole partial —
        // not a named section — is rendered.
        if ($this -> view instanceof AbstractTemplateView) {
            return $this -> view -> renderPartial('Post/Show', null, $variables);
        }

        return '';
    }

    protected function getRecordsFromCollections(array $collectionIdentifiers, array $hashtags, array $preselectedRecordsIdentifiers, int $amount, array $referenceRecords = [], string $order = ''): array
    {
        if ($amount < 0) {
            return [];
        }

        return $this -> recordCollectionEvent -> getRecordsByFilter(
            [
                'collectionIdentifiers' => $collectionIdentifiers,
                'hashtags' => $hashtags,
                'preselectedRecordIdentifiers' => $preselectedRecordsIdentifiers,
                'order' => $order ?: QueryInterface::ORDER_DESCENDING,
            ],
            $amount,
            $referenceRecords
        );
    }

    protected function getContentObjectData(int $uid = 0): array
    {
        if ($uid) {
            return $this->connectionPool
                ->getConnectionForTable('tt_content')
                ->select(
                    ['*'],
                    'tt_content',
                    ['uid' => $uid],
                ) -> fetchAssociative() ?: [];
        }
        return $this -> request -> getAttribute('currentContentObject') -> data;

    }
}
