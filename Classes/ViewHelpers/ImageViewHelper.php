<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\ViewHelpers;

use Fixpunkt\FpSocial\Domain\Model\Picture;
use Fixpunkt\FpSocial\Domain\Repository\PictureRepository;
use Fixpunkt\FpSocial\Utilities\ImageUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class ImageViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /** @var ImageUtility  */
    protected ImageUtility $imageUtility;
    /** @var PictureRepository  */
    protected PictureRepository $imageRepository;

    /**
     * @param ImageUtility $imageUtility
     */
    public function injectImageUtility(ImageUtility $imageUtility): void
    {
        $this->imageUtility = $imageUtility;
    }

    /**
     * @param PictureRepository $imageRepository
     */
    public function injectImageRepository(PictureRepository $imageRepository): void
    {
        $this -> imageRepository = $imageRepository;
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument('image', Picture::class, 'Das Bildobjekt dessen Bild ausgegeben werden soll.', true);
    }

    /**
     * Gibt ein Bild aus einem Social Media Profil aus und lädt es dazu ggf. herunter.
     * @return string
     */
    public function render(): string
    {
        /** @var Picture $image */
        $image = $this -> arguments['image'];

        // if picture not downloaded yet, get it!
        if (!$image -> getFilereference() && $this -> download($image)) {
            return '';
        }

        // add all data to viewhelper output
        if (!$image -> getFilereference()) {
            return '';
        }

        $container = $this->templateVariableContainer;
        $container->add('image', $image -> getFilereference());

        $content = $this->renderChildren();

        $container->remove('image');

        return $content;
    }

    protected function download(Picture $image): bool
    {
        try {
            /** @var FileReference $fileReference */
            $fileReference = $this -> imageUtility -> download($image);
            $image -> setFileReference($fileReference);
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }
}
