// The elFinder file manager opens in a pop-up window. The pop-up calls KmmiFileManager.onSelect(files) of this window
// (fm_elfinder.yaml: callback_function) with the chosen files: [{url, name, mime, size, hash}, ...].
const manager = {
    handler: null,

    /**
     * @param {string} url the file manager URL of an instance (see fm_elfinder.yaml)
     * @param {(files: Array<{url: string, name: string}>) => void} handler called with the chosen files
     */
    open(url, handler) {
        manager.handler = handler;
        const popup = window.open(url, 'kmmi-file-manager', 'width=1100,height=700,resizable=yes,scrollbars=yes');
        popup?.focus();
    },

    onSelect(files) {
        const handler = manager.handler;
        manager.handler = null;
        if (handler) {
            handler(files);
        }
    },
};

window.KmmiFileManager = manager;

function isImage(path) {
    return /\.(jpe?g|png|gif|webp)$/i.test(path);
}

// A text input with the data-file-chooser attribute (the file manager URL) gets a "browse" and a "clear" button
// and a thumbnail of the chosen image.
function enhanceFileChooser(input) {
    if (input.dataset.fileChooserReady) {
        return;
    }
    input.dataset.fileChooserReady = '1';

    const group = document.createElement('div');
    group.className = 'kmmi-file-chooser d-flex gap-2 align-items-center mt-1';
    const browse = document.createElement('button');
    browse.type = 'button';
    browse.className = 'btn btn-secondary';
    browse.textContent = 'Tallózás';
    const clear = document.createElement('button');
    clear.type = 'button';
    clear.className = 'btn btn-link';
    clear.textContent = 'Törlés';
    const preview = document.createElement('img');
    preview.alt = '';
    preview.style.maxHeight = '60px';

    const refresh = () => {
        const value = input.value.trim();
        preview.hidden = !isImage(value);
        preview.src = isImage(value) ? value : '';
        clear.hidden = value === '';
    };
    browse.addEventListener('click', () => {
        manager.open(input.dataset.fileChooser, (files) => {
            input.value = files[0].url;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            refresh();
        });
    });
    clear.addEventListener('click', () => {
        input.value = '';
        refresh();
    });
    input.addEventListener('input', refresh);

    group.append(browse, clear, preview);
    input.after(group);
    refresh();
}

export function initFileChoosers(root = document) {
    root.querySelectorAll('input[data-file-chooser]').forEach(enhanceFileChooser);
}

export { manager as fileManager };
