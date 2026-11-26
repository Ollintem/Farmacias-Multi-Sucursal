@props(['name', 'tipo' => 'dia', 'value' => '', 'max' => '', 'id' => null, 'placeholder' => 'Seleccionar fecha'])
@php
    $cid = $id ?? $name . '-' . $tipo;
    $valorInicial = (string) old($name, $value);
@endphp
<div
    class="relative"
    x-data='{
        tipo: @json($tipo),
        valor: @json($valorInicial),
        max: @json($max),
        abierto: false,
        vistaMes: 0,
        vistaAnio: 0,
        vistaAnioLista: 0,
        meses: ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"],
        mesesCortos: ["Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"],
        diasSem: ["Lu","Ma","Mi","Ju","Vi","Sá","Do"],
        init() {
            const hoy = new Date();
            if (this.tipo === "dia") {
                if (this.valor && /^\d{4}-\d{2}-\d{2}$/.test(this.valor)) {
                    const [y,m] = this.valor.split("-").map(Number);
                    this.vistaAnio = y; this.vistaMes = m;
                } else {
                    this.vistaAnio = hoy.getFullYear(); this.vistaMes = hoy.getMonth()+1;
                }
            } else if (this.tipo === "semana") {
                if (this.valor && /^\d{4}-W\d{2}$/.test(this.valor)) {
                    const [y,w] = this.valor.split("-W").map(Number);
                    const d = this.isoADate(y,w,1);
                    this.vistaAnio = d.getFullYear(); this.vistaMes = d.getMonth()+1;
                } else {
                    this.vistaAnio = hoy.getFullYear(); this.vistaMes = hoy.getMonth()+1;
                }
            } else if (this.tipo === "mes") {
                if (this.valor && /^\d{4}-\d{2}$/.test(this.valor)) {
                    const [y,m] = this.valor.split("-").map(Number);
                    this.vistaAnio = y;
                } else {
                    this.vistaAnio = hoy.getFullYear();
                }
                this.vistaMes = 1;
            } else if (this.tipo === "anio") {
                if (this.valor && /^\d{4}$/.test(this.valor)) {
                    this.vistaAnioLista = Math.floor(parseInt(this.valor)/12)*12;
                } else {
                    this.vistaAnioLista = Math.floor(hoy.getFullYear()/12)*12;
                }
            }
        },
        get etiqueta() {
            if (!this.valor) return @json($placeholder);
            if (this.tipo === "dia") {
                const [y,m,d] = this.valor.split("-");
                return `${d}/${m}/${y}`;
            }
            if (this.tipo === "semana") {
                const [y,w] = this.valor.split("-W");
                const ini = this.isoADate(parseInt(y), parseInt(w), 1);
                const fin = this.isoADate(parseInt(y), parseInt(w), 7);
                const fmt = (dt) => `${String(dt.getDate()).padStart(2,"0")}/${String(dt.getMonth()+1).padStart(2,"0")}`;
                return `Semana ${w}, ${y} (${fmt(ini)} - ${fmt(fin)})`;
            }
            if (this.tipo === "mes") {
                const [y,m] = this.valor.split("-");
                return `${this.meses[parseInt(m)-1]} de ${y}`;
            }
            if (this.tipo === "anio") return this.valor;
            return this.valor;
        },
        isoADate(y,w,dow){
            const jan4 = new Date(y,0,4);
            const jan4Dow = ((jan4.getDay()+6)%7)+1;
            const mondayWeek1 = new Date(jan4);
            mondayWeek1.setDate(jan4.getDate() - (jan4Dow-1));
            const res = new Date(mondayWeek1);
            res.setDate(mondayWeek1.getDate() + (w-1)*7 + (dow-1));
            return res;
        },
        isoWeek(date){
            const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNum = d.getUTCDay() || 7;
            d.setUTCDate(d.getUTCDate() + 4 - dayNum);
            const yearStart = new Date(Date.UTC(d.getUTCFullYear(),0,1));
            const week = Math.ceil(( ( (d - yearStart) / 86400000) + 1)/7);
            return [d.getUTCFullYear(), week];
        },
        isoWeekStr(date){
            const [y,w] = this.isoWeek(date);
            return `${y}-W${String(w).padStart(2,"0")}`;
        },
        esFuturoDia(y,m,d){
            if (!this.max) return false;
            if (this.tipo==="dia") return `${y}-${String(m).padStart(2,"0")}-${String(d).padStart(2,"0")}` > this.max;
            if (this.tipo==="semana") {
                const cur = this.isoWeekStr(new Date(y,m-1,d));
                return cur > this.max;
            }
            if (this.tipo==="mes") {
                const cur = `${y}-${String(m).padStart(2,"0")}`;
                return cur > this.max;
            }
            if (this.tipo==="anio") return `${y}` > this.max;
            return false;
        },
        diasMatriz(){
            const y = this.vistaAnio, m = this.vistaMes;
            const primer = new Date(y, m-1, 1);
            const offset = (primer.getDay()+6)%7;
            const dim = new Date(y, m, 0).getDate();
            const dimPrev = new Date(y, m-1, 0).getDate();
            const celdas = [];
            for (let i=0;i<42;i++){
                let dia, mes, anio, esActual;
                if (i < offset) { dia = dimPrev - offset + 1 + i; mes = m-1; anio = y; if (mes===0){mes=12; anio=y-1;} esActual=false; }
                else if (i < offset+dim) { dia = i - offset + 1; mes=m; anio=y; esActual=true; }
                else { dia = i - offset - dim +1; mes=m+1; anio=y; if(mes===13){mes=1; anio=y+1;} esActual=false; }
                celdas.push({dia, mes, anio, esActual});
            }
            return celdas;
        },
        seleccionarDia(c){
            const str = `${c.anio}-${String(c.mes).padStart(2,"0")}-${String(c.dia).padStart(2,"0")}`;
            if (this.esFuturoDia(c.anio,c.mes,c.dia)) return;
            if (this.tipo==="dia"){ this.valor=str; this.abierto=false; this.detener(); }
            if (this.tipo==="semana"){
                const d = new Date(c.anio, c.mes-1, c.dia);
                this.valor = this.isoWeekStr(d);
                this.abierto=false; this.detener();
            }
        },
        seleccionarMes(m){
            const str = `${this.vistaAnio}-${String(m).padStart(2,"0")}`;
            if (str > this.max) return;
            this.valor=str; this.abierto=false; this.detener();
        },
        seleccionarAnio(y){
            if (`${y}` > this.max) return;
            this.valor=`${y}`; this.abierto=false; this.detener();
        },
        hoyDia(){
            const h=new Date();
            const str=`${h.getFullYear()}-${String(h.getMonth()+1).padStart(2,"0")}-${String(h.getDate()).padStart(2,"0")}`;
            if (this.tipo==="dia" && str <= this.max) { this.valor=str; this.vistaAnio=h.getFullYear(); this.vistaMes=h.getMonth()+1; this.abierto=false; this.detener(); }
            if (this.tipo==="semana"){ const w=this.isoWeekStr(h); if(w<=this.max){ this.valor=w; this.vistaAnio=h.getFullYear(); this.vistaMes=h.getMonth()+1; this.abierto=false; this.detener(); } }
            if (this.tipo==="mes"){ const s=`${h.getFullYear()}-${String(h.getMonth()+1).padStart(2,"0")}`; if(s<=this.max){ this.valor=s; this.vistaAnio=h.getFullYear(); this.abierto=false; this.detener(); } }
            if (this.tipo==="anio"){ const s=`${h.getFullYear()}`; if(s<=this.max){ this.valor=s; this.abierto=false; this.detener(); } }
        },
        limpiar(){ this.valor=""; this.abierto=false; this.detener(); },
        anterior(){
            if(this.tipo==="dia"||this.tipo==="semana"){
                this.vistaMes--; if(this.vistaMes<1){this.vistaMes=12; this.vistaAnio--;}
            } else if(this.tipo==="mes"){ this.vistaAnio--; }
            else if(this.tipo==="anio"){ this.vistaAnioLista-=12; }
        },
        siguiente(){
            if(this.tipo==="dia"||this.tipo==="semana"){
                this.vistaMes++; if(this.vistaMes>12){this.vistaMes=1; this.vistaAnio++;}
            } else if(this.tipo==="mes"){ this.vistaAnio++; }
            else if(this.tipo==="anio"){ this.vistaAnioLista+=12; }
        },
        esSeleccionadoDia(c){
            if(this.tipo==="dia") return `${c.anio}-${String(c.mes).padStart(2,"0")}-${String(c.dia).padStart(2,"0")}`===this.valor;
            if(this.tipo==="semana"){
                const d=new Date(c.anio,c.mes-1,c.dia);
                return this.isoWeekStr(d)===this.valor;
            }
            return false;
        },
        esHoyDia(c){
            const h=new Date();
            return c.dia===h.getDate() && c.mes===h.getMonth()+1 && c.anio===h.getFullYear();
        },
        posicionar(){
            const rect=this.$refs.gatillo.getBoundingClientRect();
            const panel=this.$refs.panel;
            if(!panel) return;
            const alto=panel.offsetHeight;
            const ancho=Math.max(rect.width, 320);
            panel.style.left=Math.min(rect.left, window.innerWidth - ancho - 12)+"px";
            panel.style.width=ancho+"px";
            panel.style.top=(window.innerHeight - rect.bottom < alto + 12 && rect.top > alto + 12)
                ? (rect.top - 12 - alto)+"px"
                : (rect.bottom + 12)+"px";
        },
        alternar(){
            this.abierto=!this.abierto;
            if(this.abierto){
                this.$nextTick(()=>{
                    if(!this.abierto) return;
                    this.posicionar();
                    this._alMover=()=>this.posicionar();
                    window.addEventListener("scroll", this._alMover, {passive:true, capture:true});
                    window.addEventListener("resize", this._alMover);
                });
            } else { this.detener(); }
        },
        detener(){
            if(this._alMover){
                window.removeEventListener("scroll", this._alMover, {capture:true});
                window.removeEventListener("resize", this._alMover);
                this._alMover=null;
            }
        }
    }'
    @keydown.escape.window="abierto=false; detener()"
>
    <input type="hidden" name="{{ $name }}" :value="valor">
    <button
        type="button"
        x-ref="gatillo"
        @if($cid) id="{{ $cid }}" @endif
        @click="alternar()"
        :aria-expanded="abierto"
        aria-haspopup="dialog"
        class="theme-input flex w-full cursor-pointer items-center justify-between gap-2 text-left transition"
    >
        <span :class="!valor ? 'opacity-60' : 'font-semibold'" x-text="etiqueta" class="truncate"></span>
        <svg class="h-4 w-4 shrink-0 text-[#0e9384] dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 21h14a1 1 0 001-1V7a1 1 0 00-1-1H5a1 1 0 00-1 1v13a1 1 0 001 1z"/>
        </svg>
    </button>

    <template x-teleport="body">
        <div
            x-ref="panel"
            x-show="abierto"
            x-cloak
            x-transition.opacity
            @click.outside="abierto=false; detener()"
            class="fixed z-[99] overflow-hidden rounded-2xl border bg-white shadow-2xl dark:bg-slate-900"
            style="border-color: #c3d4cc; box-shadow: 0 20px 40px rgba(15,23,42,0.12);"
        >
            {{-- Header con navegación --}}
            <div class="flex items-center justify-between bg-gradient-to-r from-[#0e9384] to-[#0c7569] px-4 py-3 text-white dark:from-emerald-600 dark:to-emerald-700">
                <button type="button" @click="anterior()" class="grid h-8 w-8 place-items-center rounded-full bg-white/15 hover:bg-white/25 transition" aria-label="Anterior">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <div class="text-center">
                    <div class="text-sm font-black tracking-wide">
                        <template x-if="tipo==='dia' || tipo==='semana'">
                            <span x-text="`${meses[vistaMes-1]} ${vistaAnio}`"></span>
                        </template>
                        <template x-if="tipo==='mes'">
                            <span x-text="vistaAnio"></span>
                        </template>
                        <template x-if="tipo==='anio'">
                            <span x-text="`${vistaAnioLista} – ${vistaAnioLista+11}`"></span>
                        </template>
                    </div>
                    <div class="text-[10px] font-bold uppercase tracking-[0.15em] opacity-80" x-text="tipo==='semana' ? 'Lunes a domingo • ISO' : tipo==='dia' ? 'Selecciona un día' : tipo==='mes' ? 'Selecciona un mes' : 'Selecciona un año'"></div>
                </div>
                <button type="button" @click="siguiente()" class="grid h-8 w-8 place-items-center rounded-full bg-white/15 hover:bg-white/25 transition" aria-label="Siguiente">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <div class="p-3">
                {{-- Dia / Semana --}}
                <template x-if="tipo==='dia' || tipo==='semana'">
                    <div>
                        <div style="display:grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 2px;" class="mb-1">
                            <template x-for="d in diasSem" :key="d">
                                <span style="display:grid; place-items:center; padding: 6px 0; font-size: 11px; font-weight: 800; color: #64748b; letter-spacing: 0.05em;" x-text="d"></span>
                            </template>
                        </div>
                        <div style="display:grid; grid-template-columns: repeat(7, minmax(0,1fr)); gap: 4px;">
                            <template x-for="(c, idx) in diasMatriz()" :key="idx">
                                <button
                                    type="button"
                                    @click="seleccionarDia(c)"
                                    :disabled="esFuturoDia(c.anio,c.mes,c.dia)"
                                    :style="esSeleccionadoDia(c) ? 'background: linear-gradient(135deg,#0e9384,#0c7569); color:white; box-shadow: 0 4px 12px rgba(14,147,132,0.35);' : esHoyDia(c) && !esSeleccionadoDia(c) ? 'box-shadow: inset 0 0 0 1.5px #0e9384;' : ''"
                                    :class="esFuturoDia(c.anio,c.mes,c.dia) ? 'opacity-30 cursor-not-allowed' : ''"
                                    style="display:grid; place-items:center; height: 36px; border-radius: 9999px; font-size: 13px; font-weight: 700; transition: all 0.15s; border: none; cursor: pointer;"
                                    :class2="!c.esActual ? 'opacity-40' : ''"
                                >
                                    <span x-text="c.dia" :style="!c.esActual ? 'opacity:0.45' : ''"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Mes --}}
                <template x-if="tipo==='mes'">
                    <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 8px;">
                        <template x-for="m in 12" :key="m">
                            <button
                                type="button"
                                @click="seleccionarMes(m)"
                                :disabled="`${vistaAnio}-${String(m).padStart(2,'0')}` > max"
                                :style="valor===`${vistaAnio}-${String(m).padStart(2,'0')}` ? 'background: linear-gradient(135deg,#0e9384,#0c7569); color:white; border-color:#0e9384; box-shadow: 0 4px 12px rgba(14,147,132,0.3);' : ''"
                                style="border-radius: 14px; border: 1.5px solid #cbd5e1; padding: 12px 4px; font-size: 13px; font-weight: 800; background: white; color: #334155; cursor: pointer; transition: all 0.15s;"
                                class="hover:!border-[#0e9384] hover:!bg-emerald-50 dark:!bg-slate-800 dark:!text-slate-200 dark:!border-slate-600"
                                x-text="mesesCortos[m-1]"
                            ></button>
                        </template>
                    </div>
                </template>

                {{-- Anio --}}
                <template x-if="tipo==='anio'">
                    <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 8px;">
                        <template x-for="y in 12" :key="y">
                            <button
                                type="button"
                                @click="seleccionarAnio(vistaAnioLista + y -1)"
                                :disabled="`${vistaAnioLista + y -1}` > max"
                                :style="valor===`${vistaAnioLista + y -1}` ? 'background: linear-gradient(135deg,#0e9384,#0c7569); color:white; border-color:#0e9384;' : ''"
                                style="border-radius: 14px; border: 1.5px solid #cbd5e1; padding: 12px 4px; font-size: 13px; font-weight: 800; background: white; color: #334155; cursor: pointer;"
                                x-text="vistaAnioLista + y -1"
                            ></button>
                        </template>
                    </div>
                </template>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800" style="border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 12px;">
                    <button type="button" @click="limpiar()" style="font-size: 12px; font-weight: 800; color: #64748b; background: none; border: none; cursor: pointer;" class="hover:!text-[#0e9384]">Limpiar</button>
                    <button type="button" @click="hoyDia()" style="background: #0e9384; color: white; border-radius: 9999px; padding: 6px 14px; font-size: 12px; font-weight: 900; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(14,147,132,0.25);">Hoy</button>
                </div>
                <p style="text-align:center; font-size:10px; font-weight:700; color:#94a3b8; margin-top:8px; letter-spacing:0.05em;">No se permiten periodos futuros</p>
            </div>
        </div>
    </template>
</div>
