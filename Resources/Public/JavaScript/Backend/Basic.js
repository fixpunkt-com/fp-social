import { html } from 'lit';
import { unsafeHTML } from 'lit/directives/unsafe-html.js';
import Modal from '@typo3/backend/modal.js';
import RegularEvent from '@typo3/core/event/regular-event.js';

/**
 * Öffnet die Bilder eines Posts in einem TYPO3-Modal.
 *
 * Seit TYPO3 v14 enthält das Bootstrap-Bundle des Backends kein Modal mehr
 * (weder JavaScript noch CSS), das data-bs-toggle="modal" funktioniert also
 * nicht länger. Der Inhalt wird deshalb als <template> ausgeliefert und hier
 * über die Modal-API des Backends angezeigt.
 *
 * Das Modal wird von der Modal-API im Dokument der Backend-Shell erzeugt, also
 * außerhalb des Modul-Iframes. Deshalb greift dort auch das Bootstrap-Data-API
 * des Carousels nicht -- die Bildnavigation wird hier selbst verdrahtet (die
 * Slide-Transition ist im Backend ohnehin deaktiviert).
 */
function showPicture(carousel, index) {
    const items = Array.from(carousel.querySelectorAll('.carousel-item'));
    if (items.length === 0) {
        return;
    }

    const current = ((index % items.length) + items.length) % items.length;

    items.forEach((item, position) => {
        item.classList.toggle('active', position === current);
    });

    carousel.querySelectorAll('[data-bs-slide-to]').forEach((indicator) => {
        const active = Number(indicator.dataset.bsSlideTo) === current;
        indicator.classList.toggle('active', active);
        if (active) {
            indicator.setAttribute('aria-current', 'true');
        } else {
            indicator.removeAttribute('aria-current');
        }
    });

    carousel.dataset.fpSocialPictureCurrent = String(current);
}

function initializeCarousel(carousel, index) {
    showPicture(carousel, index);
}

new RegularEvent('click', function(event, target) {
    event.preventDefault();

    const template = document.getElementById(target.dataset.fpSocialPictureModal);
    if (template === null) {
        return;
    }

    const index = Number(target.dataset.fpSocialPictureIndex || 0);

    const modal = Modal.advanced({
        title: target.dataset.fpSocialPictureTitle || 'Bilder',
        content: html`${unsafeHTML(template.innerHTML)}`,
        size: Modal.sizes.large,
        buttons: [
            {
                text: 'Schließen',
                btnClass: 'btn-default',
                name: 'close',
                trigger: (e, openModal) => openModal.hideModal(),
            },
        ],
    });

    modal.addEventListener('typo3-modal-shown', function() {
        const carousel = modal.querySelector('.carousel');
        if (carousel !== null) {
            initializeCarousel(carousel, index);
        }

        // Das Modal hängt im Dokument der Backend-Shell. Damit das Formular zur
        // Auswahl des Vorschaubilds nicht die komplette Shell ersetzt, muss es
        // in den Browsing-Context dieses Moduls abgeschickt werden.
        const formTarget = window.parent !== window && window.name !== '' ? window.name : '_self';
        modal.querySelectorAll('form').forEach((form) => {
            form.target = formTarget;
            form.addEventListener('submit', () => modal.hideModal(), { once: true });
        });
    }, { once: true });
}).delegateTo(document, '[data-fp-social-picture-modal]');
