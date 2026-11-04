<?php

namespace App\Console\Commands;

use App\Support\CierreCajaTurno;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cajas:cerrar-vencidas')]
#[Description('Cierra las cajas que superaron la tolerancia de su turno')]
class CerrarCajasVencidas extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $cerradas = CierreCajaTurno::procesar();

        $this->info("Cajas cerradas automáticamente: {$cerradas}");

        return self::SUCCESS;
    }
}
