<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'gallery' => [
        'path' => './assets/gallery.js',
        'entrypoint' => true,
    ],
    'admin' => [
        'path' => './assets/admin/admin.js',
        'entrypoint' => true,
    ],
    'admin-login' => [
        'path' => './assets/admin/login.js',
        'entrypoint' => true,
    ],
    'trianglify' => [
        'version' => '4.1.1',
    ],
    'delaunator' => [
        'version' => '4.0.1',
    ],
    'chroma-js' => [
        'version' => '2.1.0',
    ],
    'canvas' => [
        'version' => '2.6.1',
    ],
    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/flatpickr.min.css' => [
        'version' => '4.6.13',
        'type' => 'css',
    ],
    'flatpickr/dist/l10n/hu.js' => [
        'version' => '4.6.13',
    ],
    'sortablejs' => [
        'version' => '1.15.7',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'glightbox' => [
        'version' => '3.3.1',
    ],
    'masonry-layout' => [
        'version' => '4.2.2',
    ],
    'imagesloaded' => [
        'version' => '5.0.0',
    ],
    'outlayer' => [
        'version' => '2.1.1',
    ],
    'get-size' => [
        'version' => '2.0.3',
    ],
    'ev-emitter' => [
        'version' => '2.1.2',
    ],
    'fizzy-ui-utils' => [
        'version' => '2.0.5',
    ],
    'desandro-matches-selector' => [
        'version' => '2.0.2',
    ],
    'glightbox/dist/css/glightbox.min.css' => [
        'version' => '3.3.1',
        'type' => 'css',
    ],
];
