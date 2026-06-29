# Ausgabemodi
## Liste von Posts ausgeben
Dieser Modus gibt eine feste Anzahl von Posts eines einzelenen Accouts aus.
### `Allgemeine Einstellungen` > `Social Media Account`
Der Account, von dem die Posts ausgegeben werden sollen.
### `Allgemeine Einstellungen` > `Anzahl der zu zeigenden Posts`
Die Anzahl der zu zeigenden Posts
### `Allgemeine Einstellungen` > `Offset`
Der Offset ab dem die anzuzeigenden Posts eingelesen werden. Hierbei steht 0 für den ersten Post, 1 für den zweiten.

* Möchte man die ersten drei Posts ausgeben, setzt man `Anzahl der zu zeigende Posts` auf 3 und `Offset` auf 0.
* Möchte man die drei Posts nach den drei ersten Posts ausgeben, setzt man `Anzahl der zu zeigende Posts` auf 3 und `Offset` auf 3.


## Einzelne Posts ausgeben
Dieser Modus gibt einen einzelnen Post aus.
### `Allgemeine Einstellungen` > `Social Media Account`
Der Account, von dem der Post ausgegeben werden sollen.
### `Allgemeine Einstellungen` > `Post`
Der Post, welcher angezeigt werden soll. Es kann nach der `Post Id` gesucht werden.


## Social Wall
Dieser Modus gibt eine Social Wall aus, welcher automatisch neue Posts nachlädt und die Möglichkeit bietet beliebig viele weitere Posts nachzuladen.
### `Allgemeine Einstellungen` > `Social Media Accounts`
Die Accounts, von denen die Posts ausgegeben werden sollten.

### Anzahl der anzuzeigenden Posts
Die Anzahl der anfänglich angezeigten Posts ergibt sich aus dem Produkt von Zeilen und Spalten.
#### `Einstellungen Social Wall` > `Spalten`
**Default:** 1<br>
Die Anzahl der Spalten.

#### `Einstellungen Social Wall` > `Zeilen`
**Default:** 1<br>
Die Anzahl der Zeilen.

### Nachladen
#### `Einstellungen Social Wall` > `Posts bei Neuladen ersetzen`
**Default:** Falsch<br>
Neue Posts, welche nach dem Laden der Seite erstellt worden sind ersetzen beim Nachladen den älteste, angezeigten Post. Das führt dazu, dass die Anzahl an angezeigten Posts gleichbleibt und verhindert ein "Springen" der Seite.<br>

#### `Einstellungen Social Wall` > `Manuelles Nachladen von früheren Posts aktivieren`
**Default:** Falsch<br>
Ist diese Einstellung aktiviert wird unter der Social Wall ein Button eingeblendet, welcher die Möglichkeit bietet weitere, ältere Posts zu laden. Diese erhöhen die Anzahl an angezeigten Posts und ersetzen bereits vorhadene Posts nicht.

#### `Einstellungen Social Wall` > `Beschriftung Nachladen Button`
**Default:** Weitere Posts laden<br>
Die Beschriftung des Buttons mit dem weitere, frühere Posts nachgeladen werden können.