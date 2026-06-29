# Erstellen der Netzwerkklasse
Um ein neues Soziales Netzwerk zu unterstützten, müssen Sie eine Netzwerkklasse erstellen. Dabei ist wie folgt vorzugehen:

Erstellen Sie zunächst eine eigene Extension und legen Sie das neue Netzwerk als Klasse an. Wir nennen ihn in diesem Beispiel `Vendor\Extension\Network\MeinNetzwerk`. Diese Klasse muss das Interface `\Fixpunkt\FpSocial\Network\NetworkIterface` implementieren und von der Oberklasse `\Fixpunkt\FpSocial\Network\Nework` abgeleitet werden.
```php
namespace Vendor\Extension\Network;

class MeinNetzwerk extends \Fixpunkt\FpSocial\Network\Network implements \Fixpunkt\FpSocial\Network\NetworkIterface {
    // Einlesen
    public static function getPosts(\Fixpunkt\FpSocial\Domain\Model\Account $account) : array {
        // Liste mehrerer Posts (für Formatierung der einzelnen Posts siehe weiter unten)
    }
    public static function getPost(\Fixpunkt\FpSocial\Domain\Model\Post $post, \Fixpunkt\FpSocial\Domain\Model\Account $account) : array {
        // Ein einzelner Post (für Formatierung siehe weiter unten)
    }

    // Bilderkennung
    public static function getPictureIdentifier(string $uri) : string {
        /*
        * Diese Funktion sollte jedem Bild welches aus dem sozialen Netzwerk geladen werden kann 
        * einen eindeutigen Identifier zuweisen. Häufig reicht das entfernen von Timestampangaben und Tokens aus der URL.
        */
    }

    // Erlaubte Zugänge
    public static function allowedAccess() : array {
        // Diese Funktion sollte alle Zugangsklassen zurück geben, welche mit diesem Netzwerk kompatibel sind.
        return [
            \Fixpunkt\FpSocial\Access\FpSocialServer::class,
            \Vendor\Extension\Access\MeinZugang::class
        ];
    }

    // TCA Labels
    public static function getTCALabelAccount(array $accountData) : string {
        // Darstellung von Accountdetails dieses Netzwerkes.
    }

    // Backend Preview
    public static function getBeText(\Fixpunkt\FpSocial\Domain\Model\Post $post) : string {
        // Vorschau im Backend über einen zugehörigen Post
    }
    public static function getBeInfo(\Fixpunkt\FpSocial\Domain\Model\Account $account, bool $additionalInformation = true) : string {
        // Vorschau im Backend über einen zugehörigen Account
    }
}
```

# Rückgabeformat von `getPost` und `getPosts`
Die Funktionen `getPost` und `getPosts` werden durch die Controller der Extension aufgerufe. Die Controller erwarten daher ein spezifisches Rückgabeformat.

Die Funktion `getPost` erwartet ein einzelnen Post in der Form wie unten aufgeführt. Die Funktion `getPosts` erwartet ein Array von einzelenen Posts wie unten aufgeführt.

## Rückgabeformat eines einzelnen Posts
Die Informationen eines Posts sind in folgender Art darzustellen:
```php
return [
    'message' => "Inhalt meines Posts",
    'post_url' => "Link zum Post",
    'update_time' => "Zeitpunkt, zu dem der Post erstellt wurde",
    'link' => "Link zum Post",
    // entweder
    'picture' => "URL eines Bildes",
    // oder
    'pictures' => "Array mit Bildinformationen",
    "hashtags" => [],
    "mentions" => []
];
```
Wichtig ist, dass das Array entweder den Eintrag `picture` **oder** den Eintrag `pictures` enthalten darf.


### Format der Parameter `picture` und `pictures`
Es ist sowohl möglich eine einzelne URL als auch ein Array mit mehreren URLs anzugeben. Die beiden folgenden Schreibweisen werden akzeptiert:
```php
return [
    ...
    'picture' => "https://www.fixpunkt.com/agentur-referenzen/natfak/natfak-festival-2019/grafik-design-poster-plakat-natfak-festival.jpg"
];
```
```php
return [
    ...
    'pictures' => [
        "https://www.fixpunkt.com/agentur-referenzen/natfak/natfak-festival-2019/grafik-design-poster-plakat-natfak-festival.jpg",
        "https://www.fixpunkt.com/agentur-referenzen/rgre/typo3-relaunch-rgre/relaunch-rgre-startseite-slider-icons.jpg"
    ]
];
```

### Format des Parameters `hashtags`
Ein Array mit den Strings der Hashtags ohne `#`.<br>
Beispiel:
```php
return [
    ...
    "hashtags" => ['hashtag1', 'hashtag2']
];
```

### Format des Parameters `mentions`
Ein Array welches Arrays mit den Schlüsseln `displayName` und `systemName` enthält.<br>
Beispiel: 
```php
return [
    ...
    "mentions" => [
        [
            "displayName" => "Name des Accounts", 
            "systemName" => "15865211853421"
        ],
        [
            "displayName" => "Ein weitere Account",
            "systemName" => "18534211586521"
        ]
    ]
];
```

# Registrierung der Netzwerkklasse
Damit Sie das Netzwerk nutzen können müssen Sie es nun noch registrieren. Fügen Sie dazu in Ihrer `localconf.php` folgende Zeile ein:
```php
$GLOBALS['TYPO3_CONF_VARS']["EXTENSIONS"]["networks"] = array_merge($GLOBALS['TYPO3_CONF_VARS']["EXTENSIONS"]["networks"] ?? [], [
    "\\Vendor\\Extension\\Networks\\MeinNetzwerk" => 'Mein eigenes Netzwerk',
]);
```
Wobei Sie *Mein eigenes Netzwerk* durch eine beliebige Bezeichung für Ihr Netzwerk ersetzen können.