import { fileManager } from './file-manager.js';

// The free plan license key allows only part of the CKEditor plugins: RemoveFormat, ImageResize, SimpleUploadAdapter,
// SpecialCharacters, PasteFromOffice, FindAndReplace, TableProperties, TableCellProperties, TableColumnResize,
// ListProperties, HtmlEmbed, WordCount, PageBreak and Base64UploadAdapter are refused, and a refused plugin makes the
// whole editor read-only (error license-key-plugin-not-allowed). Check a new plugin with the real key before adding it.
//
// CKEditor 5 from the CKEditor CDN (the free plan license key is valid for the CDN distribution only), loaded only
// when an editor is really needed: an editor starts when its text area becomes visible (e.g. its tab is opened).
const VERSION = '49.0.1';
const CDN = `https://cdn.ckeditor.com/ckeditor5/${VERSION}`;

let loading = null;

// The size of an image: a share of the width of the text (the drag handles of the ImageResize plugin are not allowed by
// the free license). The classes are styled in admin.css (the editor) and in the frontend styles (_global.scss).
const IMAGE_SIZES = [[25, 'Kicsi'], [50, 'Közepes'], [75, 'Nagy'], [100, 'Teljes szélesség']];

const sizeIcon = (percent) => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">'
    + '<rect x="2.5" y="3.5" width="15" height="13" fill="none" stroke="currentColor"/>'
    + `<rect x="2.5" y="3.5" width="${(15 * percent) / 100}" height="13" fill="currentColor"/></svg>`;

const imageSizeStyles = IMAGE_SIZES.map(([percent, label]) => ({
    name: `size${percent}`,
    title: `${label} (${percent}%)`,
    icon: sizeIcon(percent),
    modelElements: ['imageBlock', 'imageInline'],
    className: `image-size-${percent}`,
}));

// The text wraps around the image: it floats to the left or to the right, with a share of the width of the text. The
// image has one style (CKEditor keeps one), so a wrapped image has its size in its style too.
const WRAP_SIDES = [['left', 'Balra'], ['right', 'Jobbra']];
const WRAP_SIZES = [25, 50, 75];

const wrapIcon = (side) => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">'
    + `<rect x="${side === 'left' ? 2.5 : 11.5}" y="3.5" width="6" height="6" fill="currentColor"/>`
    + `<path d="M${side === 'left' ? '11 4.5h6.5M11 7h6.5M11 9.5h6.5' : '2.5 4.5h6.5M2.5 7h6.5M2.5 9.5h6.5'}`
    + 'M2.5 12.5h15M2.5 15h15" stroke="currentColor" fill="none"/></svg>';

const imageWrapStyles = WRAP_SIDES.flatMap(([side, label]) => WRAP_SIZES.map((percent) => ({
    name: `wrap${side}${percent}`,
    title: `${label}, körbefuttatva (${percent}%)`,
    icon: wrapIcon(side),
    modelElements: ['imageBlock', 'imageInline'],
    className: `image-${side}-${percent}`,
})));

function load() {
    loading ??= (async () => {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = `${CDN}/ckeditor5.css`;
        document.head.append(link);

        const [ckeditor, hungarian] = await Promise.all([
            import(/* webpackIgnore: true */ `${CDN}/ckeditor5.js`),
            import(/* webpackIgnore: true */ `${CDN}/translations/hu.js`),
        ]);

        return { ckeditor, translations: hungarian.default };
    })();

    return loading;
}

// Toolbar buttons that choose a file in the file manager (images and links to files).
function browsePlugin({ Plugin, ButtonView }, urls) {
    return class KmmiBrowse extends Plugin {
        init() {
            const editor = this.editor;
            const button = (name, label, instanceUrl, onFiles) => {
                editor.ui.componentFactory.add(name, (locale) => {
                    const view = new ButtonView(locale);
                    view.set({ label, withText: true, tooltip: true });
                    view.on('execute', () => fileManager.open(instanceUrl, onFiles));

                    return view;
                });
            };
            button('kmmiBrowseImage', 'Képek', urls.images, (files) => {
                editor.execute('insertImage', { source: files.map((file) => file.url) });
            });
            button('kmmiBrowseLink', 'Fájl', urls.files, (files) => {
                editor.execute('link', files[0].url);
            });
        }
    };
}

// Images pasted or dropped into the editor are uploaded to the file manager (the Simple Upload Adapter plugin is not
// part of the free license, so the upload adapter is our own).
function uploadPlugin({ Plugin, FileRepository }, uploadUrl) {
    return class KmmiUpload extends Plugin {
        static get requires() {
            return [FileRepository];
        }

        init() {
            this.editor.plugins.get(FileRepository).createUploadAdapter = (loader) => ({
                async upload() {
                    const body = new FormData();
                    body.append('upload', await loader.file);
                    const response = await fetch(uploadUrl, { method: 'POST', body });
                    const result = await response.json();
                    if (!response.ok || result.error || !result.url) {
                        throw new Error(result.error?.message ?? 'A feltöltés nem sikerült.');
                    }

                    return { default: result.url };
                },
                abort() {},
            });
        }
    };
}

async function create(textarea) {
    const licenseKey = textarea.dataset.licenseKey;
    if (!licenseKey) {
        console.warn('CKEditor: the CKEDITOR_LICENSE_KEY is not set, the plain text box is used.');

        return;
    }

    try {
        const { ckeditor: c, translations } = await load();
        const editor = await c.ClassicEditor.create(textarea, {
            licenseKey,
            language: 'hu',
            translations: [translations],
            plugins: [
                c.Essentials, c.Paragraph, c.Bold, c.Italic, c.Underline, c.Strikethrough,
                c.Link, c.AutoLink, c.Heading, c.List, c.Indent, c.IndentBlock, c.Alignment,
                c.ImageBlock, c.ImageInline, c.ImageToolbar, c.ImageCaption, c.ImageStyle, c.ImageUpload,
                c.LinkImage, c.Table, c.TableToolbar, c.MediaEmbed, c.SourceEditing, c.GeneralHtmlSupport,
                browsePlugin(c, { images: textarea.dataset.imagesUrl, files: textarea.dataset.filesUrl }),
                uploadPlugin(c, textarea.dataset.uploadUrl),
            ],
            toolbar: {
                items: [
                    'sourceEditing', '|', 'undo', 'redo', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'kmmiBrowseLink', '|', 'heading', '|',
                    'bulletedList', 'numberedList', 'outdent', 'indent', '|', 'alignment', '|',
                    'kmmiBrowseImage', 'mediaEmbed', 'insertTable',
                ],
                shouldNotGroupWhenFull: true,
            },
            image: {
                styles: { options: ['inline', 'block', ...imageSizeStyles, ...imageWrapStyles] },
                toolbar: [
                    'imageStyle:inline', 'imageStyle:block', '|',
                    ...IMAGE_SIZES.map(([percent]) => `imageStyle:size${percent}`),
                    {
                        name: 'imageStyle:wrap',
                        title: 'Szöveg körbefuttatása',
                        items: imageWrapStyles.map((style) => `imageStyle:${style.name}`),
                        defaultItem: 'imageStyle:wrapleft50',
                    },
                    '|',
                    'toggleImageCaption', 'imageTextAlternative', '|', 'linkImage',
                ],
            },
            table: { contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'] },
            mediaEmbed: { previewsInData: true },
            // The legacy contents keep all their HTML: nothing is stripped when an old article is saved again.
            htmlSupport: { allow: [{ name: /.*/, attributes: true, classes: true, styles: true }] },
        });

        const sync = () => editor.updateSourceElement();
        editor.model.document.on('change:data', sync);
        textarea.form?.addEventListener('submit', sync);
    } catch (error) {
        console.error('CKEditor could not be started, the plain text box is used.', error);
    }
}

const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        if (entry.isIntersecting && !entry.target.dataset.ckeditorStarted) {
            entry.target.dataset.ckeditorStarted = '1';
            observer.unobserve(entry.target);
            create(entry.target);
        }
    });
});

export function initRichText(root = document) {
    root.querySelectorAll('textarea[data-ckeditor]').forEach((textarea) => {
        if (!textarea.dataset.ckeditorObserved) {
            textarea.dataset.ckeditorObserved = '1';
            observer.observe(textarea);
        }
    });
}
