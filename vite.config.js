import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/ob-geolocalisation.js',
                'resources/js/ob-auth-login.js',
                'resources/js/ob-personnel-form.js',
                'resources/js/ob-personnel-index.js',
                'resources/js/ob-personnel-show.js',
                'resources/js/ob-evenement-form.js',
                'resources/js/ob-evenement-show.js',
                'resources/js/ob-cotisations-index.js',
                'resources/js/ob-vehicule-form.js',
                'resources/js/ob-organisation-organigramme.js',
                'resources/js/ob-statistique-index.js',
                'resources/js/ob-statistique-bilan.js',
                'resources/js/ob-pdf-bilan.js',
                'resources/js/ob-pdf-personnel.js',
            ],
            refresh: true,
        }),
    ],
});
