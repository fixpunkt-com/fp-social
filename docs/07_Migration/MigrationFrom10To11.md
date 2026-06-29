---
title: 10.0.x auf 11.x
---

### Änderungen in dieser Version:
* Die Datenbank-Struktur muss aktualisiert werden.
* Das Migrationsskript unter "Migration" muss ausgeführt werden.
* Der Scheduler-Task muss neu angelegt werden.
* Eventuell muss die API-URL in den Extension-Einstellungen angepasst werden. Siehe dazu auch die [Integratoren Anleitung][integratoren].
* Die Authentifizierung mit dem ``Social Server`` mit Hilfe von Benutzername und Passwort wurde entfernt. Eventuell muss also ein Zugriffsschlüssel eingetragen werden.

[integratoren]: ../05_Für_Integrator/00_Social_Server_Einstellungen.md