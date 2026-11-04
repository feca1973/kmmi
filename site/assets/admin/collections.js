import Sortable from 'sortablejs';
import { fileManager } from './file-manager.js';

// Collection editors: several files at once from the file manager (attachments, gallery images) and, for galleries,
// sorting the rows by drag and drop (the order is saved in the hidden position inputs).

function basename(path) {
    return decodeURIComponent(path.split('/').pop() ?? '');
}

function addFilesAsRows(collection, files) {
    const addButton = collection.querySelector('.field-collection-add-button');
    files.forEach((file) => {
        addButton.click();
        const items = collection.querySelectorAll('.field-collection-item');
        const row = items[items.length - 1];
        row.querySelector('input[name$="[path]"]').value = file.url;
        const name = row.querySelector('input[name$="[name]"]');
        if (name && name.value === '') {
            name.value = basename(file.url);
        }
    });
}

function initMultiAdd(collection) {
    const url = collection.querySelector('[data-kmmi-multi-add]')?.dataset.kmmiMultiAdd;
    if (!url || collection.dataset.multiAddReady) {
        return;
    }
    collection.dataset.multiAddReady = '1';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-secondary mb-2';
    button.textContent = 'Fájlok kiválasztása a fájlkezelőből';
    button.addEventListener('click', () => fileManager.open(url, (files) => addFilesAsRows(collection, files)));
    collection.prepend(button);
}

function renumberPositions(collection) {
    collection.querySelectorAll('.field-collection-item').forEach((row, index) => {
        const position = row.querySelector('input[data-position]');
        if (position) {
            position.value = String(index + 1);
        }
    });
}

function initSortable(collection) {
    const firstItem = collection.querySelector('.field-collection-item');
    if (collection.dataset.sortableReady || !collection.querySelector('input[data-position], [data-prototype]')) {
        return;
    }
    collection.dataset.sortableReady = '1';

    const container = (firstItem ?? collection.querySelector('.form-widget, div[id]')).parentElement;
    Sortable.create(container, {
        draggable: '.field-collection-item',
        handle: '.accordion-header',
        animation: 150,
        onEnd: () => renumberPositions(collection),
    });
    collection.closest('form')?.addEventListener('submit', () => renumberPositions(collection));
}

export function initCollections(root = document) {
    root.querySelectorAll('.field-collection').forEach((collection) => {
        initMultiAdd(collection);
        if (collection.classList.contains('kmmi-sortable')) {
            initSortable(collection);
        }
    });
}
