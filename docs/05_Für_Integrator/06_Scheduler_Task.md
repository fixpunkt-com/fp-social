Um die hinterlegten Accounts auf einem aktuellen Stand zu halten, können Sie [Scheduler Tasks][link_scheduler] einrichten.

# Verfügbare Tasks

Folgende Tasks stehen Ihnen zur Verfügung:
* ``fp_social::synchronize``: Synchronisiert die angelegten Accounts, wenn diese für die automatische Synchronisation ausgewählt sind.
* ``fp_social::download``: Lädt alle noch nicht heruntergeladenen Beitragsbilder herunter. Dies geschieht ansonsten beim ersten Anzeigen des jeweiligeb Beitrags.

# Einrichten eines Tasks:

1. Wechseln Sie in das Modul **Scheduler**.
2. Klicken Sie auf die Fläche zum Hinzufügen eines neuen Tasks.
3. Füllen Sie die Felder wie folgt aus:
   * **Class:** Execute console commands
   * **Frequency:** Wir empfehlen hier `*/15 * * * *`. Dies bedeutet, dass der Task alle 15 Minuten ausgeführt wird.
   * **CommandController Command:** Wählen Sie hier einen der oben genannten Tasks aus.
4. Speichern Sie den Task und prüfen Sie diesen, indem Sie ihn einmalig manuell ausführen.

**ACHTUNG:**<br>
Vergewissern Sie sich, dass Sie den Scheduler aufrufen. Beachten Sie bitte auch, dass der Task nur so häufig durchgeführt werden kann, wie der Scheduler aufgerufen wird.<br>
Sollte der Scheduler nur alle 30 Minuten aufgerufen werden, so werden auch die Tasks nur alle 30 Minuten aufgerufen, unabhängig ihrer Einstellung unter *Frequency*.

Mehr Informationen zum Scheduler finden Sie [hier][link_scheduler].

[link_scheduler]: https://docs.typo3.org/c/typo3/cms-scheduler/master/en-us/