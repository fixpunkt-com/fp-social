Befolgen Sie die folgenden Schritte um die Extension `fp_social` korrekt einzurichten.

* Installieren Sie die Extension in Ihrer TYPO3 Instanz:
  * *Klassisch:* Legen Sie die Extension in Ihren `typo3_conf` Ordner ab und installieren diese manuell im `Extension Manager`.
  * *Composer:* Laden Sie die Extension `fixpunkt/fp-social` aus unserem Composer-Repository unter `https://composer.fixpunkt.com` herunter*.
* Fügen Sie Ihrem Template das Static Template `fp_social` hinzu.
  * Ergänzen Sie die TypoScript-Konstanten `plugin.tx_fpsocial.persistence.storagePid` sowie `module.tx_fpsocial.persistence.storagePid`.
* Legen Sie einen [Scheduler Task][scheduler] an, damit die hinterlegten Social Media Accounts automatisch synchronisiert werden.
* Legen Sie Ihren [ersten Zugang und Social Media Account][first_step] an.

<small>* um Zugriff auf das Composer-Repository zu erhalten benötigen Sie Ihre individuellen Zugangsdaten.</small>

[scheduler]: 05_Für_Integrator/06_Scheduler_Task.md
[first_step]: 03_Backend_Verwaltung/01_Zugänge_und_Accounts_verwalten.md