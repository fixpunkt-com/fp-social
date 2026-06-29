Derzeit werden durch _fp_social_ von Haus aus folgende sozialen Netzwerke unterstützt:

## Unterstützte Netzwerke
* Facebook
    * Import von Posts von Seiten, deren Administrator*in man ist.
* Instagram
    * Das eigene Profil
    * Aktuelle sowie Top-Posts eines Hashtags
* Wordpress
    * Alle Einträge eines Blogs.
    * Alle Einträge, welche mit einem gewissen Tag versehen sind.
    * Alle Einträge eines bestimmten Autors.
* Youtube
    * Letzte Einträge eines Profils.
* LinkedIn
    * Import von Posts von Seiten, deren Administrator*in man ist.

# Datenschutz
Um die Besucher*innen Ihrer Website vor Tracking durch soziale Netzwerke zu schützen gibt `fp_social` die Posts nicht direkt über die sozialen Netzwerke aus.

![alt_text][schaubild]

Die Ausgabe der Posts erfolgt wie folgt:
* Posts werden gemäß der angelegten Accounts in die Datenbank Ihrer TYPO3-Instanz synchronisiert.
* Daten, welche dem Benutzer zur Verfügung gestellt werden, werden aus der TYPO3-Instanz ausgelesen und ausgegeben.

Somit ist sichergestellt, dass die sozialen Netzwerke keine Cookies auf dem Computer der Besucher*innen hinterlegen können und auch kein Tracking möglich ist.

[schaubild]: schaubild.png "Schaubild welches zeigt, dass keine Kommunikation zwischen Webseiten-Besucher*in und sozialem Netzwerk stattfindet."