<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Command;

use Fixpunkt\FpSocial\Domain\Model\Picture;
use Fixpunkt\FpSocial\Domain\Repository\PictureRepository;
use Fixpunkt\FpSocial\Events\PostUpdatedEvent;
use Fixpunkt\FpSocial\Utilities\ImageUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

class DownloadImages extends Command
{
    /** @var PictureRepository */
    protected PictureRepository $pictureRepository;
    /** @var ImageUtility  */
    protected ImageUtility $imageUtility;
    /** @var PersistenceManager  */
    protected PersistenceManager $persistenceManager;

    /**
     * Synchronize constructor.
     * @param string|null $name
     */
    public function __construct(ImageUtility $imageUtility, PictureRepository $pictureRepository, PersistenceManager $persistenceManager, ?string $name = null)
    {
        parent::__construct($name);
        $this -> imageUtility = $imageUtility;
        $this -> pictureRepository = $pictureRepository;
        $this -> persistenceManager = $persistenceManager;

        // Repository Settings anpassen
        /** @var Typo3QuerySettings $querySettings */
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings -> setRespectStoragePage(false);
        $this -> pictureRepository -> setDefaultQuerySettings($querySettings);
    }

    /**
     * Konfiguriert das Kommando.
     */
    protected function configure()
    {
        $this->setDescription('Downloads missing Images from Posts.')
            ->addArgument(
                'amount',
                InputArgument::OPTIONAL,
                'Amount of Pictures to download.'
            );
    }

    /**
     * Führt das Kommando aus und synchronisiert Accounts.
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $amount = $input->getArgument('amount') ? (int)$input->getArgument('amount') : 40;
        $pictures = $this -> pictureRepository -> findForCommandTask($amount);

        /** @var Picture $picture */
        foreach ($pictures as $picture) {
            try {
                // Execution Time hochsetzen, denn das einlesen kann dauern!
                set_time_limit(240);
                $this -> imageUtility -> download($picture);
                $this -> cleareCache($picture);
            } catch (\Exception | \TypeError $e) {
            }
        }

        // Falls Bilder gelöscht wurden, müssen diese Änderungen persistiert werden.
        $this -> persistenceManager -> persistAll();
        return 0;
    }

    /**
     * Clears the frontend cache of a given post.
     * @param Picture $picture
     */
    private function cleareCache(Picture $picture): void
    {
        GeneralUtility::makeInstance(PostUpdatedEvent::class, $picture -> getPost());
    }
}
