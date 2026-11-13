@if(session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mb-4 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 dark:text-emerald-400"
        role="status"
    >
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-600 dark:text-red-400"
        role="alert"
    >
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-500/25 bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300" role="alert">
        <p class="font-semibold">Revisa la información capturada:</p>
        <ul class="mt-1 list-disc space-y-1 pl-5 text-xs">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
