# Synchronisierung verwalten

Die Einstellung zur Synchronisation der Accounts können Sie einfach und schnell im Backend-Modul **Social-Media Verwaltung** unter dem Reiter **Web** bearbeiten.

![alt text][list]
Hier können Sie für jeden Account die Synchronisation sowie das Verhalten beim hinzufügen von neuen Posts festlegen.

## Automatische Synchronisation
Ist diese Option aktiviert, wird dieser Account bei der Synchronisation über den [Scheduler Task][scheduler] berücksichtigt. Ist diese Option deaktiviert muss der Account manuell über das Backend Module synchronisiert werden.

## Automatische Freischalten
Ist diese Option aktiviert, werden alle eingelesenen Posts automatisch freigeschaltet und im Frontend angezeigt.
Diese Option bezieht sich sowohl auf manuelle als auch mit Hilfe des [Schedulers][scheduler] eingelesene Posts.

[scheduler]: ../05_Für_Integrator/06_Scheduler_Task.md
[list]: images/synchronisation.png "Logo Title Text 2"