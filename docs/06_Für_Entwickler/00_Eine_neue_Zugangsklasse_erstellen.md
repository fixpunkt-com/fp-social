# Erstellen der Zugangsklasse
Für bestehende Soziale Netzwerke können neue Zugänge erstellt werden. Dabei ist wie folgt vorzugehen:

Erstellen Sie zunächst eine eigene Extension und legen Sie den neuen Zugang als Klasse an. Wir nennen ihn in diesem Beispiel `Vendor\Extension\Access\MeinZugang`. Diese Klasse muss das Interface `\Fixpunkt\FpSocial\Access\Access` implementieren.
```php
namespace Vendor\Extension\Access;

class MeinZugang implements \Fixpunkt\FpSocial\Access\Access {
    /**
     * Gibt die unveränderten Daten eines Posts zurück.
     * @param \Fixpunkt\FpSocial\Domain\Model\Post $post
     * @param \Fixpunkt\FpSocial\Domain\Model\Account $account
     * @return mixed
     */
    public static function getPostRawData(\Fixpunkt\FpSocial\Domain\Model\Post $post, \Fixpunkt\FpSocial\Domain\Model\Account $account) {
        // Implementierung
    }

    /**
     * Gibt die unveränderten Daten eines Accounts zurück.
     * @param \Fixpunkt\FpSocial\Domain\Model\Account $account
     * @param string $position
     * @return mixed
     */
    public static function getPostsRawData(\Fixpunkt\FpSocial\Domain\Model\Account $account, string $position = "0") {
        // Implementierung
    }

    /**
     * Gibt ein Label für einen Zugang zurück.
     * @param array $data
     * @return string
     */
    public static function getTCALabelAccount(array $data) : string {
        // Implementierung
    }
}
```
Welche Rückgabe von den beiden oben aufgeführten Funktionen erwartet wird, ist abhängig vom Sozialen Netzwerk. Orientiere Sie sich an bereits bestehenden Klassen.

# Registrierung der Zugangsklasse
Damit Sie den Zugang benutzen können müssen Sie ihn nun noch registrieren. Fügen Sie dazu in Ihrer `ext_localconf.php` folgende Zeile ein:
```php
$GLOBALS['TYPO3_CONF_VARS']["EXTENSIONS"]["access"] = array_merge($GLOBALS['TYPO3_CONF_VARS']["EXTENSIONS"]["access"] ?? [], [
    "\\Vendor\\Extension\\Access\\MeinZugang" => 'Mein eigener Zugang',
]);
```
Wobei Sie *Mein eigener Zugang* durch eine beliebige Bezeichung für Ihren Zugang ersetzen können.