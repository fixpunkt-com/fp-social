import DocumentService from '@typo3/core/document-service.js';
import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import Modal from '@typo3/backend/modal.js';
import Notification from '@typo3/backend/notification.js';
import FormEngine from '@typo3/backend/form-engine.js';
import { selector } from '@typo3/core/literals.js';
import { html } from 'lit';
// Das PHP-Element rendert <typo3-backend-icon>; der Import registriert die Web-Component.
import '@typo3/backend/element/icon-element.js';

/**
 * Holt die beim Social Server verbundenen Zugaenge und traegt einen Kanal in die
 * TCA-Felder ein.
 *
 * Die Feldnamen kommen aus PHP (Classes/Form/FieldControl/AccountPicker.php) und werden
 * hier nicht geraten - bei noch ungespeicherten Datensaetzen steckt in ihnen eine
 * NEW...-Uid.
 *
 * Das Modal wird von der Modal-API im Dokument der Backend-Shell erzeugt, also ausserhalb
 * des Modul-Iframes (dasselbe Thema wie in Basic.js). Die Klick-Handler sind aber hier
 * definiert und schreiben deshalb korrekt in das Formular dieses Iframes.
 */
class AccountPicker {
    constructor(buttonSelector, options) {
        this.options = options;
        this.modal = null;
        this.loading = false;

        DocumentService.ready().then(() => {
            const button = document.querySelector(buttonSelector);
            if (button === null) {
                return;
            }
            this.button = button;
            button.addEventListener('click', (event) => {
                event.preventDefault();
                this.open();
            });
        });
    }

    /**
     * Liest ein Formularfeld. Text-Eingaben rendert FormEngine doppelt: sichtbar mit
     * data-formengine-input-name, dazu ein verstecktes Feld mit dem tatsaechlich
     * abgeschickten Wert unter "name". Auswahlfelder haben nur "name".
     */
    getField(name) {
        return document.querySelector(selector`[data-formengine-input-name="${name}"]`)
            ?? document.querySelector(selector`[name="${name}"]`);
    }

    open() {
        // Erst beim Klick lesen: Zugangsdaten und Netzwerk koennen seit dem Aufbau des
        // Formulars ungespeichert geaendert worden sein.
        const accessField = this.getField(this.options.fieldNames.access);
        const accessUid = accessField === null ? 0 : parseInt(accessField.value, 10) || 0;
        if (accessUid === 0) {
            Notification.warning(this.options.labels.title, this.options.labels.noAccess);
            return;
        }

        // Der Social Server fragt die Netzwerk-Apis live ab, das dauert. Doppelklicks
        // wuerden sonst mehrere Abfragen ausloesen.
        if (this.loading) {
            return;
        }
        this.setLoading(true);

        new AjaxRequest(TYPO3.settings.ajaxUrls[this.options.ajaxUrlKey])
            .withQueryArguments({ access: accessUid })
            .get()
            .then(async (response) => {
                const resolved = await response.resolve();
                this.setLoading(false);
                this.showModal(resolved.accounts || []);
            })
            .catch(async (response) => {
                this.setLoading(false);
                let message = response.message;
                if (typeof message === 'undefined') {
                    const resolved = await response.resolve();
                    message = resolved.errorMessage;
                }
                Notification.error(this.options.labels.title, message);
            });
    }

    setLoading(loading) {
        this.loading = loading;
        if (!this.button) {
            return;
        }
        this.button.disabled = loading;
        this.button.setAttribute('aria-busy', loading ? 'true' : 'false');

        // Der Social Server fragt die Netzwerk-Apis live ab, das dauert spuerbar - ohne
        // Rueckmeldung wirkt der Button kaputt.
        const label = this.button.querySelector('span');
        if (label !== null) {
            label.textContent = loading ? this.options.labels.loading : this.options.labels.button;
        }
    }

    showModal(accounts) {
        const currentNetwork = this.getField(this.options.fieldNames.network)?.value ?? '';

        // Nach Netzwerk gruppieren, damit man sofort im richtigen Block landet. Die
        // Reihenfolge stammt aus der Antwort des Social Servers und bleibt damit stabil.
        // Gruppiert wird nach accountClass und nicht nach networkKey: derselbe Zugang kann
        // unter mehreren Netzwerken auftauchen (Instagram nutzt den Facebook-Schluessel mit).
        const groups = new Map();
        accounts.forEach((account) => {
            if (!groups.has(account.accountClass)) {
                groups.set(account.accountClass, { label: account.accountLabel, accounts: [] });
            }
            groups.get(account.accountClass).accounts.push(account);
        });

        const sections = [];

        groups.forEach((group, accountClass) => {
            // Ein Zugang eines anderen Netzwerks stellt beim Klick das Feld "network" um -
            // und das speichert und laedt das Formular neu. Der Hinweis steht einmal am
            // Block statt an jeder einzelnen Zeile.
            const switchesNetwork = accountClass !== currentNetwork;
            const rows = [];

            group.accounts.forEach((account) => {
                const heading = account.displayName || account.username;

                if (account.channels.length === 0) {
                    rows.push(html`<div class="list-group-item text-body-secondary">
                        <strong>${heading}</strong>
                        <div>${this.options.labels.noChannels}</div>
                    </div>`);
                    return;
                }

                account.channels.forEach((channel) => {
                    rows.push(html`<button type="button" class="list-group-item list-group-item-action"
                        @click=${() => this.apply(account, channel, switchesNetwork)}>
                        <strong>${channel.name || channel.id}</strong>
                        <div class="text-body-secondary">
                            ${heading} &middot; <code>${channel.id}</code>
                        </div>
                        ${account.expired
                            ? html`<div class="text-danger">${this.options.labels.expired}</div>`
                            : (account.expires
                                ? html`<div class="text-body-secondary">${this.options.labels.expires} ${account.expires}</div>`
                                : '')}
                    </button>`);
                });
            });

            sections.push(html`<div class="mb-3">
                <h2 class="h5 mb-1">${group.label}</h2>
                ${switchesNetwork
                    ? html`<p class="text-warning mb-2">${this.options.labels.switchesNetwork} ${group.label}</p>`
                    : ''}
                <div class="list-group">${rows}</div>
            </div>`);
        });

        if (sections.length === 0) {
            Notification.info(this.options.labels.title, this.options.labels.noChannels);
            return;
        }

        this.modal = Modal.advanced({
            title: this.options.labels.title,
            content: html`<div>${sections}</div>`,
            size: Modal.sizes.medium,
            buttons: [
                {
                    text: this.options.labels.close,
                    btnClass: 'btn-default',
                    name: 'close',
                    trigger: (event, openModal) => openModal.hideModal(),
                },
            ],
        });
    }

    apply(account, channel, switchesNetwork) {
        // Reihenfolge ist wichtig: das Netzwerk zuletzt. Dessen change-Event loest ueber
        // onChange => reload sofort das Speichern aus, danach sind wir hier fertig.
        this.write(this.options.fieldNames.label, channel.name || channel.id);
        this.write(this.options.fieldNames.channel, channel.id);

        if (switchesNetwork) {
            this.writeNetwork(account.accountClass);
        }

        if (this.modal) {
            this.modal.hideModal();
        }
        if (!switchesNetwork) {
            Notification.success(this.options.labels.title, this.options.labels.applied);
        }
    }

    /**
     * Schreibt in ein Text-Eingabefeld. Das change-Event darf nicht fehlen: daran haengt
     * FormEngineValidation.initializeInputField() den Abgleich in das versteckte Feld,
     * das den Wert tatsaechlich abschickt.
     */
    write(name, value) {
        const field = this.getField(name);
        if (field === null) {
            // Das Feld gehoert nicht zur Palette des aktuellen Netzwerks und ist deshalb
            // gar nicht gerendert - etwa "label" bei Youtube und Bluesky.
            return;
        }
        field.value = value;
        field.dispatchEvent(new Event('change'));
        this.markAsChanged(field);
    }

    /**
     * Stellt das Netzwerk um. Das change-Event am Auswahlfeld laeuft ueber
     * FormEngine.processOnFieldChange() in den reload-Handler von TYPO3, der das Formular
     * speichert und neu laedt - die eben geschriebenen Werte gehen dabei mit.
     */
    writeNetwork(accountClass) {
        const field = this.getField(this.options.fieldNames.network);
        if (field === null) {
            return;
        }
        field.value = accountClass;
        this.markAsChanged(field);
        field.dispatchEvent(new Event('change'));
    }

    /**
     * markFieldAsChanged() sitzt erst ab v14 an FormEngine, in v12 und v13 nur an
     * FormEngineValidation.
     */
    markAsChanged(field) {
        FormEngine.Validation.validateField(field);
        if (typeof FormEngine.markFieldAsChanged === 'function') {
            FormEngine.markFieldAsChanged(field);
        } else {
            FormEngine.Validation.markFieldAsChanged(field);
        }
    }
}

export default AccountPicker;
