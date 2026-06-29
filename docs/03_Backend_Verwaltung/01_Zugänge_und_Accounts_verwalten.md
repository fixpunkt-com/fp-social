Derzeit ist es leider noch nicht möglich Zugänge und Accounts über das Backend-Modul zuverwalten.

Um Posts aus einem Social Media Profil zu importieren, müssen Sie zunächst angeben, wie Sie auf das soziale Netzwerk zugreifen möchten und danach ihre Account-Details angeben.

<sub>Wie Sie eigene Zugangsklassen oder weitere soziale Netzwerke hinzufügen können, erfahre Sie [hier][link_developer]</sub>

## Zugang einrichten
Abhängig vom gewählten Netzwerk stehen Ihnen verschiedene Zugänge zur Verfügung. Welche Zugänge mit welchen sozialen Netzwerken kompatibel sind erfahren Sie [hier][link_compatiblity].

So richten Sie einen Zugang ein:
1. Wechseln Sie in das Modul **Liste** und navigieren Sie zum Ordner, in dem Sie die Posts aus dem Netzwerk speichern möchten.<br>
   ![alt text][list]
2. Klicken Sie auf "Neuen Rekord hinzufügen" und wählen Sie **fp_social > Zugangsdaten**.<br>
   ![alt text][add_access]
3. Wählen Sie den gewünschten Zugang aus und tätigen Sie alle benötigten Angaben.<br>
   ![alt text][fill_access]
4. Speichern Sie Ihre Änderungen.

## Account einrichten
Nachdem Sie einen Zugang angelegt haben, können Sie nun den Account anlegen für den Sie diese Zugangsdaten nutzen möchten.

Dazu gehen Sie wie folgt vor:

1. Wechseln Sie in das Modul **Liste** und navigieren Sie zum Ordner, in dem Sie die Posts aus dem Netzwerk speichern möchten.<br>
   ![alt text][list]
2. Klicken Sie auf "Neuen Rekord hinzufügen" und wählen Sie **fp_social > Account**.<br>
   ![alt text][add_account]
3. Füllen Sie die Felder nun wie folgt aus:
   * **Grunddaten:**
     * **Netzwerk:** Das Netzwerk, aus dem Sie den Account synchronisieren möchten.
     * **Zugangsdaten:** Die zuvor angelegten Zugangsdaten. Je nachdem, welches Netzwerk Sie ausgewählt haben ändern sich hier die Auswahlmöglichkeiten.
     * **Beschriftung:** Der Anzeigename des Accounts.
     * **Weitere Felder:** Je nach ausgewählten Netzwerk müssen weitere Angaben gemacht werden. Klicken Sie auf das Label um weitere Hilfeleistungen zu erhalten.
   * **Synchronisation:**
     * **Diesen Account automatisch synchronisieren:** Ist diese Option ausgewählt, wird der Account automatisch synchronisiert (wie die automatische Synchronisierung eingerichtet wird erfahren Sie [hier][link_sheduler]) 
     * **Eingelesene Posts werden sofort freigeschaltet:** Ist diese Option ausgewählt, werden alle eingelesenen Posts automatisch freigeschaltet (und somit im Frontend sichtbar). Ansonsten müssen diese händisch freigeschaltet werden.
     * **Datum der letzten Synchronisation:** Der Zeitpunkt des letzten Synchronisationsversuchs. Lassen Sie dieses Feld leer.
     * **Datum der letzten erfolgreichen Synchronisation:** Der Zeitpunkt der letzten erfolgreichen Synchronisation. Lassen Sie dieses Feld leer.
   * **Posts:** Lassen Sie dieses Feld leer
   ![alt text][fill_account]
   
4. Speichern Sie Ihre Änderungen.

[list]: images/modules.png "Logo Title Text 2"
[add_access]: images/add_access.png "Logo Title Text 2"
[add_account]: images/add_account.png "Logo Title Text 2"
[fill_access]: images/fill_access.png "Logo Title Text 2"
[fill_account]: images/fill_account.png "Logo Title Text 2"

[link_compatiblity]: ../01_Unterstütze_Netzwerke.md
[link_developer]: ../06_Für_Entwickler/00_Eine_neue_Zugangsklasse_erstellen.md
[link_sheduler]: ../05_Für_Integrator/06_Scheduler_Task.md