import { BrowserMultiFormatReader } from '@zxing/browser';

const createScannerDialog = () => {
    const dialog = document.createElement('dialog');
    dialog.id = 'barcode-scanner-dialog';
    dialog.className = 'w-[min(92vw,36rem)] rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/70';
    dialog.innerHTML = `
        <div class="overflow-hidden rounded-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Cámara</p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-900">Escanear código de barras</h2>
                </div>
                <button type="button" data-scanner-close class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Cerrar</button>
            </div>
            <div class="bg-slate-950 p-4">
                <div class="relative overflow-hidden rounded-xl bg-black">
                    <video data-scanner-video class="block aspect-video w-full object-cover" muted playsinline></video>
                    <div class="pointer-events-none absolute inset-[15%] rounded-lg border-2 border-emerald-400 shadow-[0_0_0_9999px_rgba(2,6,23,0.35)]"></div>
                </div>
                <p data-scanner-status class="mt-3 text-center text-sm text-slate-300">Solicitando acceso a la cámara...</p>
            </div>
        </div>
    `;

    document.body.append(dialog);

    return dialog;
};

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.getElementById('scanner-focus-button');
    const barcodeInput = document.getElementById('barcode-input');

    if (! trigger || ! barcodeInput) {
        return;
    }

    const dialog = createScannerDialog();
    const video = dialog.querySelector('[data-scanner-video]');
    const status = dialog.querySelector('[data-scanner-status]');
    const reader = new BrowserMultiFormatReader();
    let controls = null;

    const stopScanner = () => {
        controls?.stop();
        controls = null;
        video.srcObject?.getTracks().forEach((track) => track.stop());
        video.srcObject = null;
    };

    const closeScanner = () => {
        stopScanner();
        dialog.close();
    };

    dialog.querySelector('[data-scanner-close]').addEventListener('click', closeScanner);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeScanner();
    });
    dialog.addEventListener('close', stopScanner);

    trigger.addEventListener('click', async () => {
        barcodeInput.focus();
        barcodeInput.select();

        if (! navigator.mediaDevices?.getUserMedia) {
            window.alert('Este navegador no permite usar la cámara. Puedes escribir el código o conectar un lector USB.');
            return;
        }

        status.textContent = 'Solicitando acceso a la cámara...';
        dialog.showModal();

        try {
            controls = await reader.decodeFromConstraints(
                { video: { facingMode: { ideal: 'environment' } } },
                video,
                (result) => {
                    if (! result) {
                        return;
                    }

                    barcodeInput.value = result.getText();
                    barcodeInput.dispatchEvent(new Event('input', { bubbles: true }));
                    closeScanner();
                    barcodeInput.focus();
                },
            );
            status.textContent = 'Enfoca el código dentro del recuadro.';
        } catch (error) {
            stopScanner();
            status.textContent = 'No se pudo iniciar la cámara. Revisa el permiso del navegador y usa HTTPS o localhost.';
            console.error('No se pudo iniciar el lector de códigos:', error);
        }
    });
});