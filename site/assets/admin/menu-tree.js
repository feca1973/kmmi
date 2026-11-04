import Sortable from 'sortablejs';

// The menu editor: the items of a menu as a tree of up to three levels. The rows are sorted by dragging, also between
// the levels (an item dropped into the list under another item becomes its sub item), and are added and removed here.
// The legacy admin did the same. The names of the inputs are numbered by the place of the rows on the screen, so the
// server saves the tree as it is seen (see MenuItemsFormType).

const GROUP = 'kmmi-menu';
const ITEM = '.kmmi-menu-item';
const LIST = '.kmmi-menu-list';

function levelOf(list) {
    let level = 1;
    for (let parent = list.parentElement?.closest(LIST); parent; parent = parent.parentElement?.closest(LIST)) {
        level += 1;
    }

    return level;
}

// The number of the levels an item takes with its sub items.
function heightOf(item) {
    const nested = [...item.querySelectorAll(`:scope > ${LIST} > ${ITEM}`)];

    return 1 + Math.max(0, ...nested.map(heightOf));
}

function itemsOf(list) {
    return [...list.children].filter((element) => element.matches(ITEM));
}

function listOf(item) {
    return item.querySelector(`:scope > ${LIST}`);
}

function initTree(tree) {
    const maxLevel = Number(tree.dataset.maxLevel);
    const rootList = tree.querySelector(`:scope > ${LIST}`);

    // Every item that can have sub items has a list for them (an empty one is a place to drop an item into).
    const ensureChildList = (item, level) => {
        if (level >= maxLevel || listOf(item)) {
            return;
        }
        const list = document.createElement('ol');
        list.className = 'kmmi-menu-list list-unstyled';
        item.append(list);
        makeSortable(list);
    };

    const renumber = () => {
        const walk = (list, prefix) => {
            const level = levelOf(list);
            itemsOf(list).forEach((item, index) => {
                const base = `${prefix}[${index}]`;
                item.dataset.level = String(level);
                item.querySelectorAll(`:scope > .kmmi-menu-row [data-menu-field]`).forEach((input) => {
                    input.name = `${base}[${input.dataset.menuField}]`;
                    input.id = input.name.replace(/\]\[|\[|\]/g, '_').replace(/_$/, '');
                });
                ensureChildList(item, level);
                const nested = listOf(item);
                if (nested) {
                    walk(nested, `${base}[children]`);
                }
            });
        };
        walk(rootList, tree.dataset.name);
    };

    function makeSortable(list) {
        if (list.dataset.sortableReady) {
            return;
        }
        list.dataset.sortableReady = '1';
        Sortable.create(list, {
            group: GROUP,
            handle: '.kmmi-menu-handle',
            draggable: ITEM,
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            onStart: () => document.body.classList.add('kmmi-menu-dragging'),
            // Not into itself, and not so deep that the item with its sub items would have more levels than allowed.
            onMove: (event) => !event.dragged.contains(event.to)
                && levelOf(event.to) + heightOf(event.dragged) - 1 <= maxLevel,
            onEnd: () => {
                document.body.classList.remove('kmmi-menu-dragging');
                renumber();
            },
        });
    }

    const addItem = (list) => {
        // (data-prototype-1 is not in the dataset under a camel case name: a digit does not start a new word.)
        const prototype = tree.getAttribute(`data-prototype-${levelOf(list)}`);
        const holder = document.createElement('template');
        holder.innerHTML = prototype ?? '';
        const item = holder.content.firstElementChild;
        if (!item) {
            return;
        }
        list.append(item);
        item.querySelectorAll(LIST).forEach(makeSortable);
        renumber();
        item.querySelector('[data-menu-field="title"]')?.focus();
    };

    tree.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('button') : null;
        if (!button || !tree.contains(button)) {
            return;
        }
        const item = button.closest(ITEM);
        if (button.matches('.kmmi-menu-add')) {
            addItem(rootList);
        } else if (button.matches('.kmmi-menu-add-child') && item) {
            ensureChildList(item, levelOf(item.parentElement));
            const children = listOf(item);
            if (children) {
                addItem(children);
            }
        } else if (button.matches('.kmmi-menu-remove') && item && window.confirm(tree.dataset.confirm)) {
            item.remove();
            renumber();
        }
    });

    tree.closest('form')?.addEventListener('submit', renumber);
    tree.querySelectorAll(LIST).forEach(makeSortable);
    renumber();
}

export function initMenuTrees(root = document) {
    root.querySelectorAll('.kmmi-menu-tree').forEach((tree) => {
        if (tree.dataset.treeReady) {
            return;
        }
        tree.dataset.treeReady = '1';
        initTree(tree);
    });
}
