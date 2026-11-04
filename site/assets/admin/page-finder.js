// The page finder of the menu editor: while the editor types the target of a menu item, the pages of the site that
// match are offered (the server looks them up); a path (/...), a "#" or an address (http...) is taken as it is.
// Choosing a page fills the target and, when the item has no name yet, the name too.

const DELAY = 200;
const MIN_LENGTH = 2;
const DIRECT = /^(#|\/|https?:)/i;

// The list is in the body, under the input: inside the row it would be cut off by the accordion of the collection.
function createList() {
    const list = document.createElement('div');
    list.className = 'list-group position-absolute shadow';
    list.style.zIndex = '2000';
    list.style.maxHeight = '260px';
    list.style.overflowY = 'auto';
    list.setAttribute('role', 'listbox');
    list.hidden = true;
    document.body.append(list);

    return list;
}

function placeUnder(list, input) {
    const box = input.getBoundingClientRect();
    list.style.left = `${box.left + window.scrollX}px`;
    list.style.top = `${box.bottom + window.scrollY}px`;
    list.style.width = `${box.width}px`;
}

function pageItem(page) {
    const item = document.createElement('button');
    item.type = 'button';
    item.className = 'list-group-item list-group-item-action';
    item.setAttribute('role', 'option');

    const title = document.createElement('strong');
    title.textContent = page.title;
    const detail = document.createElement('small');
    detail.className = 'd-block text-muted';
    detail.textContent = `${page.kind} · ${page.url}`;
    item.append(title, detail);

    return item;
}

function initPageFinder(input) {
    if (input.dataset.pageSearchReady) {
        return;
    }
    input.dataset.pageSearchReady = '1';

    const list = createList();
    let timer = null;
    let latest = 0;
    let active = -1;
    let pages = [];

    const close = () => {
        list.hidden = true;
        list.replaceChildren();
        active = -1;
        pages = [];
    };

    const choose = (page) => {
        input.value = page.url;
        const title = input.closest('.kmmi-menu-item')?.querySelector('[data-menu-field="title"]');
        if (title && title.value.trim() === '') {
            title.value = page.title;
        }
        close();
    };

    const highlight = (index) => {
        active = index;
        [...list.children].forEach((item, position) => item.classList.toggle('active', position === index));
        list.children[index]?.scrollIntoView({ block: 'nearest' });
    };

    const render = (found) => {
        pages = found;
        active = -1;
        if (found.length === 0) {
            const none = document.createElement('div');
            none.className = 'list-group-item text-muted';
            none.textContent = 'Nincs találat';
            list.replaceChildren(none);
        } else {
            list.replaceChildren(...found.map((page) => {
                const item = pageItem(page);
                // The input keeps the focus, so the list is not closed before the click arrives.
                item.addEventListener('mousedown', (event) => event.preventDefault());
                item.addEventListener('click', () => choose(page));

                return item;
            }));
        }
        placeUnder(list, input);
        list.hidden = false;
    };

    const search = (term) => {
        const request = ++latest;
        fetch(`${input.dataset.kmmiPageSearch}?term=${encodeURIComponent(term)}`, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
            .then((response) => (response.ok ? response.json() : []))
            .then((found) => {
                if (request === latest) {
                    render(found);
                }
            })
            .catch(() => {
                if (request === latest) {
                    close();
                }
            });
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        latest += 1;
        const term = input.value.trim();
        if (term.length < MIN_LENGTH || DIRECT.test(term)) {
            close();

            return;
        }
        timer = setTimeout(() => search(term), DELAY);
    });

    input.addEventListener('keydown', (event) => {
        if (list.hidden || pages.length === 0) {
            return;
        }
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            const step = event.key === 'ArrowDown' ? 1 : -1;
            highlight((active + step + pages.length) % pages.length);
        } else if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            choose(pages[active]);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            close();
        }
    });

    input.addEventListener('blur', close);
    window.addEventListener('resize', () => placeUnder(list, input));
}

export function initPageFinders(root = document) {
    root.querySelectorAll('input[data-kmmi-page-search]').forEach(initPageFinder);
}
