import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";

export default defineConfig({
    plugins: [
        /* plugin per l'integrazione con Symfony */
        symfonyPlugin(),
    ],

    // Altre configurazioni di Vite...
    build: {
        rollupOptions: {
            input: {
                // Definisci qui i tuoi "entrypoint"
                app: "./assets/app.js"
            },
        }
    },
});