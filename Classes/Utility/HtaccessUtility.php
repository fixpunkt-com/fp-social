<?php

declare(strict_types=1);

namespace Fixpunkt\FpFileprotector\Utility;

use Fixpunkt\FpFileprotector\Resource\ResourceStorage;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class HtaccessUtility
{
    public function __construct(protected readonly ResourceStorage $resourceStorage) {}

    /**
     * Checks whether the storage is protected by an .htaccess file.
     *
     * @return bool
     */
    public function hasHtaccess(): bool
    {
        return $this->getHtaccessPosition() >= 0;
    }

    /**
     * Adds protection rules to an .htaccess file if they do not exist yet.
     */
    public function addHtaccess(): void
    {
        if ($this->hasHtaccess()) {
            return;
        }

        $path = $this->getHtaccessPath();
        if ((!file_exists($path) && !is_writable(dirname($path))) || (file_exists($path) && !is_writable($path))) {
            return;
        }

        $handle = fopen($path, 'a');
        if (!$handle) {
            return;
        }

        foreach ($this->getHtaccessTemplate() as $templateLine) {
            fwrite($handle, "\n" . $templateLine);
        }
        fclose($handle);
    }

    /**
     * Removes the protection rules from an .htaccess file.
     */
    public function removeHtaccess(): void
    {
        if (!$this->hasHtaccess()) {
            return;
        }

        $position = $this->getHtaccessPosition();
        $templateLines = count($this->getHtaccessTemplate());
        $path = $this->getHtaccessPath();
        if (!is_readable($path) || !is_writable($path)) {
            return;
        }

        $newFile = [];
        $handle = fopen($path, 'r');
        if ($handle) {
            $i = 0;
            while (($buffer = fgets($handle)) !== false) {
                if ($i < $position || $i > $position + $templateLines - 1) {
                    $newFile[] = $buffer;
                }
                $i++;
            }
            fclose($handle);
        }

        $handle = fopen($path, 'w');
        if (!$handle) {
            return;
        }

        foreach ($newFile as $line) {
            fwrite($handle, $line);
        }
        fclose($handle);
    }

    /**
     * Returns whether the .htaccess file exists.
     *
     * @return bool
     */
    private function htaccessExists(): bool
    {
        return file_exists($this->getHtaccessPath());
    }

    /**
     * Returns the possible .htaccess file path.
     *
     * @return string
     */
    private function getHtaccessPath(): string
    {
        return Environment::getPublicPath() . '/' . $this->resourceStorage->getRootLevelFolder()->getPublicUrl() . '.htaccess';
    }

    /**
     * Returns the folder protection template.
     *
     * @return array
     */
    private function getHtaccessTemplate(): array
    {
        $templatePath = ExtensionManagementUtility::extPath('fp_fileprotector') . 'Resources/Private/htaccess.txt';
        $lines = [];
        if (!is_readable($templatePath)) {
            return $lines;
        }

        $handle = fopen($templatePath, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $lines[] = trim($line);
            }
            fclose($handle);
        }
        return $lines;
    }

    private function getHtaccessPosition(): int
    {
        if (!$this->htaccessExists()) {
            return -1;
        }

        $template = $this->getHtaccessTemplate();
        $templateLine = -1;
        $firstLine = -1;
        $path = $this->getHtaccessPath();
        if (!is_readable($path)) {
            return $firstLine;
        }

        $handle = fopen($path, 'r');
        if ($handle) {
            $i = 0;
            while (($line = fgets($handle)) !== false) {
                $buffer = trim($line);

                if ($templateLine >= count($template)) {
                    fclose($handle);
                    return $firstLine;
                }
                if ($templateLine >= 0) {
                    if (array_key_exists($templateLine, $template) && $buffer === $template[$templateLine]) {
                        $templateLine++;
                    } else {
                        $templateLine = -1;
                        $firstLine = -1;
                    }
                }
                if (array_key_exists(0, $template) && $templateLine < 0 && $buffer === $template[0]) {
                    $firstLine = $i;
                    $templateLine = 1;
                }

                $i++;
            }
            fclose($handle);
        }

        return $firstLine;
    }
}
