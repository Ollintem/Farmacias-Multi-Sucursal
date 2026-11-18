@php($title = 'Reporte de ventas')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <script>
            (() => {
                document.documentElement.classList.remove('dark', 'theme-dark');
                document.documentElement.classList.add('theme-light', 'light');
                document.documentElement.style.colorScheme = 'light';
            })();
        </script>
        <style>
            @media print {
                @page {
                    size: A4 landscape;
                    margin: 10mm;
                }

                html,
                body {
                    background: #fff !important;
                }

                body * {
                    visibility: visible !important;
                }

                .no-print {
                    display: none !important;
                }

                #reporte-pdf {
                    width: 100% !important;
                    max-width: none !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                #reporte-pdf .module-card {
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 0 !important;
                    box-shadow: none !important;
                }

                #reporte-pdf .module-card,
                #reporte-pdf .module-card * {
                    background-color: #fff !important;
                    color: #0f172a !important;
                    border-color: #cbd5e1 !important;
                }

                #reporte-pdf .entradas-tabla {
                    min-width: 0 !important;
                    font-size: 10px !important;
                }

                #reporte-pdf .entradas-tabla thead th,
                #reporte-pdf .entradas-tabla tbody td,
                #reporte-pdf .entradas-tabla tfoot td {
                    padding: 5px 6px !important;
                }

                #reporte-pdf thead {
                    display: table-header-group;
                }

                #reporte-pdf tr {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>
    <body class="bg-white text-slate-900">
        <div id="reporte-pdf" class="mx-auto max-w-[1180px] p-6 sm:p-8">
            <header class="mb-5 flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-4">
                <div class="flex items-start gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-[#0e9384] text-xs font-black text-white">Rx</span>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-[#0c7569]">FarmaERP</p>
                        <h1 class="mt-0.5 text-xl font-black tracking-tight text-slate-900">Reporte de ventas</h1>
                        <p class="mt-1 text-sm font-medium text-slate-500">
                            {{ $reporte['etiqueta'] }} ·
                            {{ $reporte['sucursal']?->nombre_sucursal ?? 'Todas las sucursales' }}
                        </p>
                    </div>
                </div>

                <div class="text-right text-xs text-slate-500">
                    <p class="font-bold uppercase tracking-widest text-slate-600">Generado</p>
                    <p class="mt-1 font-semibold text-slate-800">{{ now()->format('d/m/Y H:i') }}</p>
                    <p class="mt-0.5">{{ $reporte['totales']['cantidad'] }} venta(s) · {{ number_format((int) $reporte['totales']['unidades']) }} unidad(es)</p>
                </div>
            </header>

            @include('pages.reportes.components.tabla-reporte', ['reporte' => $reporte])

            <footer class="mt-5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 pt-3 text-[11px] text-slate-500">
                <span>Periodo consultado: {{ $reporte['etiqueta'] }}</span>
                <span>Documento generado el {{ now()->format('d/m/Y H:i') }}</span>
            </footer>
        </div>

        <div class="no-print fixed bottom-5 right-5 z-30 flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
            <span class="px-2 text-xs font-semibold text-slate-500">Selecciona «Guardar como PDF» en el diálogo.</span>
            <button type="button" onclick="window.print()" class="theme-button theme-button-primary !min-h-0 !py-2">
                Imprimir / Guardar PDF
            </button>
        </div>

        <script>
            (() => {
                const prepararImpresion = () => {
                    document.documentElement.classList.remove('dark', 'theme-dark');
                    document.documentElement.classList.add('theme-light', 'light');
                    document.documentElement.style.colorScheme = 'light';
                };

                document.addEventListener('DOMContentLoaded', prepararImpresion);

                window.addEventListener('load', () => {
                    prepararImpresion();
                    window.setTimeout(() => window.print(), 350);
                });
            })();
        </script>
    </body>
</html>
