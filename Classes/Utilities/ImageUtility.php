<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Utilities;

use Fixpunkt\FpSocial\Domain\Model\Picture;
use Fixpunkt\FpSocial\Domain\Repository\PictureRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFileNameException;
use TYPO3\CMS\Core\Resource\Exception\ExistingTargetFolderException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderWritePermissionsException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class ImageUtility
{
    /** @var ResourceStorage|null  */
    protected ?ResourceStorage $storage = null;
    /** @var FileRepository|null  */
    protected ?FileRepository $fileRepository = null;
    /** @var PictureRepository|null  */
    protected ?PictureRepository $pictureRepository = null;

    /**
     * ImageUtility constructor.
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     */
    public function __construct(FileRepository $fileRepository, PictureRepository $pictureRepository)
    {
        $this -> storage = $this -> getStorage();
        $this -> fileRepository = $fileRepository;
        $this -> pictureRepository = $pictureRepository;

        $this -> createFolder();
    }

    /**
     * Speichert ein Bild in den Speicher.
     * @param Picture $picture
     * @return ExtbaseFileReference|null
     * @throws InsufficientFolderAccessPermissionsException
     * @throws ExistingTargetFileNameException
     * @throws \Exception
     */
    public function download(Picture $picture): ?ExtbaseFileReference
    {
        $fileFormat = $this -> getFileFormat($picture -> getUri());

        // Datei herunterladen
        try {
            $tmpFile = $this -> loadToTmp($picture, $fileFormat);
        } catch (\Exception $e) {
            $this -> removePicture($picture);
            return null;
        }

        // Datei in Speicher verschieben
        /** @var File $newFile */
        $newFile = $this -> storage -> addFile(
            $tmpFile,
            $this -> storage -> getFolder('fp_social'),
            $picture -> getUid() . '.' . $fileFormat
        );

        // File Reference anlegen
        return $this -> createFileReference($picture, $newFile);
    }

    /**
     * Gibt den Speicher zurück, in dem die Bilder abgelegt werden sollen.
     * @return ResourceStorage
     */
    private function getStorage(): ResourceStorage
    {
        /** @var StorageRepository $storageRepository */
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        return $storageRepository -> getDefaultStorage();
    }

    /**
     * Legt den Unterordner an in dem die Bilder gespeichert werden.
     * @throws ExistingTargetFolderException
     * @throws InsufficientFolderAccessPermissionsException
     * @throws InsufficientFolderWritePermissionsException
     */
    private function createFolder(): void
    {
        if (!$this -> storage -> hasFolder('fp_social')) {
            $this -> storage -> createFolder('fp_social');
        }
    }

    /**
     * Downloaded das Bild und speichert es zwischen.
     * @param Picture $picture
     * @param string $fileFormat
     * @return string
     * @throws \Exception
     * @throws ClientException
     */
    private function loadToTmp(Picture $picture, string $fileFormat): string
    {
        $this -> createTmpFolder();
        $fileName = Environment::getPublicPath() . '/uploads/tx_fpsocial/tmp-' . $picture -> getUid() . '.' . $fileFormat;

        // Datei herunterladen
        $client = new Client();
        $response = $client -> get($picture -> getUri());
        $download = file_put_contents($fileName, (string)$response -> getBody());

        // Prüfen ob etwas heruntergeladen wurde.
        if ($download === false) {
            throw new \Exception('Die Datei konnte nicht heruntergeladen werden!');
        }

        // Datei auf Bild prüfen
        $imageSize = getimagesize($fileName);
        if (!($imageSize[0] ?? false)) {
            throw new \Exception('Die heruntergeladene Datei ist kein Bild!', 1634718085);
        }
        if ($imageSize[0] < 10 || $imageSize[1] < 10) {
            throw new \Exception('Die heruntergeladene Bild ist zu klein!', 1634718086);
        }
        return $fileName;
    }

    /**
     * Erstellt eine FileReference zwischen der Datei und dem Picture.
     * @param Picture $picture
     * @param File $file
     * @return ExtbaseFileReference
     * @throws \Exception
     */
    private function createFileReference(Picture $picture, File $file): ExtbaseFileReference
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        $affectedRows = $queryBuilder
            ->insert('sys_file_reference')
            ->values([
                'pid' => $picture -> getPid(),
                'tablenames' => 'tx_fpsocial_domain_model_picture',
                'fieldname' => 'filereference',
                'uid_local' => $file -> getUid(),
                'uid_foreign' => $picture -> getUid(),
                'tstamp' => time(),
                'crdate' => time(),
            ])
            ->executeStatement();
        if ($affectedRows == 0) {
            throw new \Exception('Es konnte keine FileReference angelegt werden!');
        }

        // File Reference ausgeben
        $lastInsertUid = $queryBuilder->getConnection()->lastInsertId();

        $fileObjects = $this -> fileRepository -> findByRelation('tx_fpsocial_domain_model_picture', 'filereference', $picture -> getUid());

        /** @var FileReference $fileReference */
        foreach ($fileObjects as $fileReference) {
            if ($fileReference -> getUid() == $lastInsertUid) {
                $extbaseFileReference = GeneralUtility::makeInstance(ExtbaseFileReference::class);
                $extbaseFileReference -> setOriginalResource($fileReference);
                return $extbaseFileReference;
            }
        }
        throw new \Exception('Die gewünschte FileReference konnte nicht gefunden werden!');
    }

    /**
     * Ermittelt das Dateiformat des Bildes
     * @param string $uri
     * @return string
     */
    private function getFileFormat(string $uri): string
    {
        $urlSegments = explode('?', $uri);
        $firstPart = array_shift($urlSegments);
        $parts = explode('.', $firstPart);
        $format = array_pop($parts);
        return !in_array($format, explode(',', $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'])) ? 'jpg' : $format;
    }

    /**
     * Erstellt die notwendigen Tmp-Ordner.
     */
    private function createTmpFolder(): void
    {
        if (!file_exists(Environment::getPublicPath() . '/uploads')) {
            mkdir(Environment::getPublicPath() . '/uploads');
        }
        if (!file_exists(Environment::getPublicPath() . '/uploads/tx_fpsocial')) {
            mkdir(Environment::getPublicPath() . '/uploads/tx_fpsocial');
        }
    }

    /**
     * Entfernt ein Picture-Objekt wenn dessen Download-Link abgelaufen ist und es somit nicht mehr nützlich ist.
     * @param Picture $picture
     * @throws IllegalObjectTypeException
     */
    private function removePicture(Picture $picture): void
    {
        // Wir entfernen das Bild weil es sowieso nicht mehr heruntergeladen werden kann!
        $this -> pictureRepository -> remove($picture);
    }
}
