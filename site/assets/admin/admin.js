import flatpickr from 'flatpickr';
import * as huLocale from 'flatpickr/dist/l10n/hu.js';
import 'flatpickr/dist/flatpickr.min.css';
import { initFileChoosers } from './file-manager.js';
import { initRichText } from './ckeditor.js';
import { initCollections } from './collections.js';
import { initPageFinders } from './page-finder.js';
import { initMenuTrees } from './menu-tree.js';

// Date and time pickers in Hungarian: no native browser inputs (see AGENTS.md). The inputs are marked with the
// `datetimepicker` / `datepicker` class by LegacyCrudController::dateTimeField() / dateField().
const hungarian = huLocale.Hungarian ?? huLocale.default?.hu ?? huLocale.default;

function initPickers(root = document) {
    root.querySelectorAll('input.datetimepicker').forEach((input) => {
        if (input._flatpickr) {
            return;
        }
        flatpickr(input, {
            locale: hungarian,
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            allowInput: true,
        });
    });
    root.querySelectorAll('input.datepicker').forEach((input) => {
        if (input._flatpickr) {
            return;
        }
        flatpickr(input, { locale: hungarian, dateFormat: 'Y-m-d', allowInput: true });
    });
}

function initAll(root = document) {
    initPickers(root);
    initFileChoosers(root);
    initRichText(root);
    initCollections(root);
    initPageFinders(root);
    initMenuTrees(root);
}

document.addEventListener('DOMContentLoaded', () => {
    initAll();

    // Rows added later (collections) get the same widgets.
    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => mutation.addedNodes.forEach((node) => {
            if (node instanceof Element) {
                initAll(node);
            }
        }));
    }).observe(document.body, { childList: true, subtree: true });
});
