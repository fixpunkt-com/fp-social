<?php

declare(strict_types=1);

namespace Fixpunkt\FpSocial\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Grosser Button am Kopf der Einstellungs-Palette, der die beim Social Server verbundenen
 * Kanaele anbietet und Kanal-Id und -Name in "channel" und "label" eintraegt.
 *
 * Das TCA-Feld dazu hat bewusst keine Spalte in ext_tables.sql: es speichert nichts, es ist
 * nur der Aufhaenger fuer den Button. Das traegt, weil DatabaseRowDefaultValues jede Spalte
 * aus der processedTca mit '' vorbelegt, und weil DefaultTcaSchema fuer type=user keine
 * Spalte anlegt - der Schema-Analyzer meldet also auch nichts an.
 *
 * Absichtlich ohne wrapWithFieldsetAndLegend(): die Palette rendert kein eigenes Label, der
 * Button traegt seine Beschriftung selbst. Ein zusaetzliches Feld-Label waere doppelt.
 *
 * Das Icon kommt als Web-Component und nicht ueber die IconFactory: deren getIcon() erwartet
 * in v12 einen String und ab v14 zwingend ein IconSize-Enum, das es in v12 noch nicht gibt.
 * <typo3-backend-icon> gibt es dagegen in allen drei Versionen mit denselben Attributen.
 *
 * Absichtlich ohne Konstruktor: die NodeFactory erzeugt den Knoten mit
 * GeneralUtility::makeInstance() ohne Argumente und uebergibt die Daten per setData().
 * In TYPO3 v12 gibt es dafuer noch den deprecated Default-Konstruktor - so laeuft die
 * Klasse unveraendert auf v12, v13 und v14.
 */
final class AccountPickerElement extends AbstractFormElement
{
    /** @var string */
    private const LL = 'LLL:EXT:fp_social/Resources/Private/Language/locallang.xlf:';

    /**
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render(): array
    {
        $resultArray = $this -> initializeResultArray();

        $ownField = (string)($this -> data['parameterArray']['itemFormElName'] ?? '');

        // Geschwisterfelder aus dem eigenen Feldnamen ableiten: data[tabelle][uid][feld].
        // Das haelt auch bei noch ungespeicherten Datensaetzen, deren Uid "NEW..." lautet.
        [$prefix] = GeneralUtility::revExplode('[', $ownField, 2);

        $id = StringUtility::getUniqueId('t3js-fpsocial-accountpicker-');
        $languageService = $this -> getLanguageService();
        $buttonLabel = $languageService -> sL(self::LL . 'accountPicker.button');
        $description = $languageService -> sL(self::LL . 'accountPicker.description');

        $html = [];
        $html[] = '<div class="form-control-wrap">';
        $html[] =   '<button type="button" class="btn btn-primary btn-lg" id="' . htmlspecialchars($id) . '">';
        $html[] =       '<typo3-backend-icon identifier="actions-cloud-download" size="small"></typo3-backend-icon>';
        $html[] =       '<span>' . htmlspecialchars($buttonLabel) . '</span>';
        $html[] =   '</button>';
        $html[] =   '<p class="form-text">' . htmlspecialchars($description) . '</p>';
        $html[] = '</div>';

        $resultArray['html'] = implode(LF, $html);
        $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create('@fixpunkt/fp-social/AccountPicker.js')
            -> instance('#' . $id, [
                'ajaxUrlKey' => 'fpsocial_accounts',
                'fieldNames' => [
                    'access' => $prefix . '[access]',
                    'network' => $prefix . '[network]',
                    'label' => $prefix . '[label]',
                    'channel' => $prefix . '[channel]',
                ],
                // Labels aus PHP mitgeben: v14 liefert Backend-Labels ueber "~labels/..."-Module,
                // v12 und v13 ueber TYPO3.lang. Der Weg ueber die Optionen funktioniert auf allen
                // drei Versionen gleich.
                'labels' => [
                    'button' => $buttonLabel,
                    'title' => $languageService -> sL(self::LL . 'accountPicker.modal.title'),
                    'close' => $languageService -> sL(self::LL . 'accountPicker.modal.close'),
                    'noAccess' => $languageService -> sL(self::LL . 'accountPicker.noAccess'),
                    'noChannels' => $languageService -> sL(self::LL . 'accountPicker.noChannels'),
                    'loading' => $languageService -> sL(self::LL . 'accountPicker.loading'),
                    'expired' => $languageService -> sL(self::LL . 'accountPicker.expired'),
                    'expires' => $languageService -> sL(self::LL . 'accountPicker.expires'),
                    'applied' => $languageService -> sL(self::LL . 'accountPicker.applied'),
                    'switchesNetwork' => $languageService -> sL(self::LL . 'accountPicker.switchesNetwork'),
                ],
            ]);

        return $resultArray;
    }
}
