import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';

function getLocalIp() {
    const interfaces = os.networkInterfaces();
    const candidates = [];

    for (const [name, addrs] of Object.entries(interfaces)) {
        if (!addrs) continue;
        for (const addr of addrs) {
            if (addr.family === 'IPv4' && !addr.internal) {
                candidates.push({ name, address: addr.address });
            }
        }
    }

    // Prefer Wi-Fi or Ethernet adapters over virtual or internal adapters
    const preferred = candidates.find(c =>
        !/virtual|vethernet|wsl|docker|vbox|vmware|loopback/i.test(c.name)
    );

    return preferred?.address || candidates[0]?.address || 'localhost';
}

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const host = env.VITE_HMR_HOST || env.VITE_HOST || getLocalIp();

    return {
        server: {
            host: '0.0.0.0',
            cors: true,
            allowedHosts: true,
            hmr: {
                host: host,
            },
        },
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
    };
});

