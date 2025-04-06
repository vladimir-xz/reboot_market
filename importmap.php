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
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '7.3.0',
    ],
    '@symfony/ux-live-component' => [
        'path' => './vendor/symfony/ux-live-component/assets/dist/live_controller.js',
    ],
    '@tailwindcss/forms' => [
        'version' => '0.5.10',
    ],
    'mini-svg-data-uri' => [
        'version' => '1.4.4',
    ],
    'tailwindcss/plugin' => [
        'version' => '3.4.17',
    ],
    'tailwindcss/defaultTheme' => [
        'version' => '3.4.17',
    ],
    'tailwindcss/colors' => [
        'version' => '3.4.17',
    ],
    'picocolors' => [
        'version' => '1.1.1',
    ],
    '@stripe/stripe-js' => [
        'version' => '6.1.0',
    ],
    'unplugin-fonts' => [
        'version' => '1.3.1',
    ],
    'unplugin' => [
        'version' => '2.0.0-beta.1',
    ],
    'fast-glob' => [
        'version' => '3.3.2',
    ],
];
