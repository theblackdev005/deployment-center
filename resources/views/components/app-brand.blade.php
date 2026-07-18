@props(['subtitle' => false, 'centered' => false])

@php
    $logo = config('branding.logo');
    $hasLogo = filled($logo) && is_file(public_path(ltrim($logo, '/')));
@endphp

<span {{ $attributes->class(['flex items-center gap-3', 'justify-center' => $centered]) }}>
    @if($hasLogo)
        <img src="{{ $logo }}" alt="{{ config('app.name') }}" class="max-h-10 max-w-40 object-contain">
    @else
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-[#673de6] text-sm font-bold text-white">DC</span>
    @endif
    @unless($hasLogo)
        <span>
            <span class="block text-sm font-bold text-slate-950">{{ config('app.name', 'Deploy Center') }}</span>
            @if($subtitle)<span class="block text-xs text-slate-400">Gestion des publications</span>@endif
        </span>
    @endunless
</span>
