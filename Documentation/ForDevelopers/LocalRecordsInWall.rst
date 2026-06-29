..  _local-records-in-wall:

=================================
Local records in the Social Wall
=================================

The :ref:`plugin-wall` can output not only synchronized social media posts, but
also arbitrary local records from your own (or third-party) extensions – for
example news, events or products. Such records are merged with the social media
posts, sorted by date, and they take part in the automatic and manual loading of
the wall. Individual records can additionally be output with the
:ref:`plugin-post` plugin.

To achieve this, your extension registers a *record source* with *fp_social*.
This chapter describes the required building blocks.

How it works
============

*fp_social* dispatches the PSR-14 event
``\Fixpunkt\FpSocial\Events\RecordCollectionEvent``. An event listener in your
extension registers a source by calling ``addSource()`` on the event:

..  code-block:: php

    $event->addSource(
        'myrecords',   // unique source identifier
        'myrecords',   // prefix used for collections (see "Identifier convention")
        'myrecords',   // prefix used for single records (see "Identifier convention")
        \Vendor\Extension\Social\RecordRepository::class
    );

A source consists of three classes you provide:

*   a **repository** implementing ``RecordRepositoryInterface`` that returns the
    selectable items for the backend and the actual records for the frontend,
*   a **record proxy** implementing ``RecordInterface`` that adapts each of your
    records to the structure *fp_social* expects, and
*   an **account proxy** extending ``\Fixpunkt\FpSocial\Domain\Model\Account``
    that provides the meta information (label, icon, …) shown for the records.

Identifier convention
======================

Records and collections are referenced as strings of the form
``<prefix>:<uid>``, e.g. ``myrecords:42``. The second and third argument of
``addSource()`` define the prefixes *fp_social* uses to recognize which entries
belong to your source. *fp_social* strips the prefix and passes the bare uids to
your repository.

*   **Collections** are the entries shown in the *Social media accounts* field
    of the Wall plugin. Use them to let editors choose *which* set of records is
    output (for example a category).
*   **Records** are individual entries shown in the *Fixed records* field of the
    Wall plugin and in the single *Post* field of the :ref:`plugin-post` plugin.

Step 1: The record proxy
========================

Implement ``\Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface`` to wrap a
single record of your extension:

..  code-block:: php

    <?php
    namespace Vendor\Extension\Social;

    use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
    use Fixpunkt\FpSocial\Domain\Model\Account;
    use Fixpunkt\FpSocial\Domain\Model\Picture;
    use TYPO3\CMS\Core\Utility\GeneralUtility;
    use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

    class RecordProxy implements RecordInterface
    {
        public function __construct(private readonly MyRecord $record) {}

        public function getIdentifier(): string { return 'myrecords:' . $this->record->getUid(); }
        public function getId(): string { return (string)$this->record->getUid(); }
        public function getNetwork(): string { return 'My records'; }
        public function getAccount(): Account { return GeneralUtility::makeInstance(AccountProxy::class); }
        public function getUrl(): string { return $this->buildDetailUri(); }
        public function getLink(): string { return $this->getUrl(); }
        public function getUpdatedTime() { return $this->record->getDatetime(); } // must be a \DateTime
        public function getHeadline(): string { return $this->record->getTitle(); }
        public function getMessage(): string { return $this->record->getText(); }
        public function getPicture(): string { return ''; } // URL of the preview image

        public function getPictures()
        {
            $pictures = new ObjectStorage();
            if ($this->record->getImage()) {
                $picture = new Picture();
                $picture->setFileReference($this->record->getImage());
                $pictures->attach($picture);
            }
            return $pictures;
        }

        public function getSelectedOrFirstPicture() { return $this->getPictures()->current(); }
        public function asJson(): array { return []; }
    }

A few notes:

*   ``getUpdatedTime()`` must return a ``\DateTime``. It is used to sort the
    records across all sources and to drive the pagination of the wall.
*   ``getPictures()`` returns an ``ObjectStorage`` of
    ``\Fixpunkt\FpSocial\Domain\Model\Picture`` objects; attach a
    ``sys_file_reference`` to each via ``setFileReference()``.
*   ``asJson()`` is only used for the AJAX output and may return an empty array.

Step 2: The account proxy
=========================

The post templates render meta information (source name, icon, link, partial
folder) through the record's account. Provide an account proxy by extending the
abstract ``Account`` model:

..  code-block:: php

    <?php
    namespace Vendor\Extension\Social;

    use Fixpunkt\FpSocial\Domain\Model\Account;
    use TYPO3\CMS\Core\SingletonInterface;

    class AccountProxy extends Account implements SingletonInterface
    {
        public static function getDescription(): string { return 'My records'; }
        public static function getPartialFolder(): string { return 'MyRecords'; }

        public function getChannelUri(): string { return ''; }
        public function getChannelLink(): string { return ''; }
        public static function getPictureIdentifier(string $uri): string { return $uri; }
        public static function getTCALabelAccount(int $uid): string { return ''; }
    }

``getDescription()`` is shown as the source label (for example in the post
footer), and ``getPartialFolder()`` controls the CSS class and template variant
used when rendering the record. The remaining abstract methods can return empty
values if they are not relevant for your records.

Step 3: The record repository
=============================

Implement ``\Fixpunkt\FpSocial\Domain\Interfaces\RecordRepositoryInterface``
with four methods – two for the backend selection and two for the frontend
output:

..  code-block:: php

    <?php
    namespace Vendor\Extension\Social;

    use Fixpunkt\FpSocial\Domain\Interfaces\RecordInterface;
    use Fixpunkt\FpSocial\Domain\Interfaces\RecordRepositoryInterface;

    class RecordRepository implements RecordRepositoryInterface
    {
        // Backend: entries for the "Social media accounts" field.
        public function getCollectionsForTca(): array
        {
            return [
                ['label' => 'My records', 'value' => 'myrecords:1'],
            ];
        }

        // Backend: entries for the "Fixed records" and single "Post" fields.
        public function getAllRecordsForTca(): array
        {
            $items = [];
            foreach ($this->findAll() as $record) {
                $items[] = [
                    'label' => 'My record: ' . $record->getTitle(),
                    'value' => 'myrecords:' . $record->getUid(),
                ];
            }
            return $items;
        }

        // Frontend: load specific records by their (prefix-stripped) uids.
        public function getRecordsByIdentifiers(array $identifiers): array
        {
            $records = [];
            foreach ($this->findByUids($identifiers) as $record) {
                $records[] = new RecordProxy($record);
            }
            return $records;
        }

        // Frontend: load records for the wall, honoring filter, limit and pagination.
        public function getRecordsByFilter(array $filter, int $limit, ?RecordInterface $referenceRecord): array
        {
            // $filter['collectionIdentifiers']        selected collection ids of this source
            // $filter['preselectedRecordIdentifiers'] uids already shown as fixed records (exclude them)
            // $filter['hashtags']                     selected hashtag uids (ignore if not applicable)
            // $filter['order']                        QueryInterface::ORDER_DESCENDING | ORDER_ASCENDING
            // $referenceRecord                        load records older/newer than this one (pagination)
            $records = [];
            foreach ($this->query($filter, $limit, $referenceRecord) as $record) {
                $records[] = new RecordProxy($record);
            }
            return $records;
        }
    }

In ``getRecordsByFilter()`` you should:

*   exclude the uids in ``$filter['preselectedRecordIdentifiers']`` (they are
    already output as fixed records),
*   order the records by date according to ``$filter['order']``,
*   respect ``$limit``, and
*   when ``$referenceRecord`` is given, only return records that are older (for
    ``ORDER_DESCENDING``) or newer than ``$referenceRecord->getUpdatedTime()`` –
    this drives the "load more" and "load newer" functionality.

*fp_social* merges and re-sorts the records of all sources by
``getUpdatedTime()`` afterwards.

Step 4: Register the source
===========================

Create an event listener for the ``RecordCollectionEvent`` and register it as a
service.

..  code-block:: php

    <?php
    namespace Vendor\Extension\EventListener;

    use Fixpunkt\FpSocial\Events\RecordCollectionEvent;
    use Vendor\Extension\Social\RecordRepository;

    final class RegisterRecordSource
    {
        public function __invoke(RecordCollectionEvent $event): void
        {
            $event->addSource('myrecords', 'myrecords', 'myrecords', RecordRepository::class);
        }
    }

..  code-block:: yaml

    # Configuration/Services.yaml
    services:
      _defaults:
        autowire: true
        autoconfigure: true
        public: true

      Vendor\Extension\:
        resource: '../Classes/*'
        exclude: '../Classes/Social/*'

      Vendor\Extension\EventListener\RegisterRecordSource:
        tags:
          - name: event.listener
            identifier: 'myextension-fpsocial-recordsource'

..  important::
    Exclude the proxy and repository classes from the service container (here
    ``../Classes/Social/*``). They are instantiated manually – per record and
    with constructor arguments – and must not be registered as shared services.

Result
======

Once the source is registered, your records become selectable in the Wall
plugin: as a collection under *Social media accounts* and individually under
*Fixed records*. Single records can also be output with the :ref:`plugin-post`
plugin. In the frontend they are rendered with the standard post templates,
merged with the social media posts and sorted by date.
