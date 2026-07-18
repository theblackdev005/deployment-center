@props([
    'type' => 'success',
    'message',
    'autoDismiss' => false,
])

@php
    $styles = [
        'success' => ['border-emerald-200 bg-emerald-50 text-emerald-800', 'check-circle-2'],
        'error' => ['border-red-200 bg-red-50 text-red-800', 'alert-triangle'],
        'warning' => ['border-amber-200 bg-amber-50 text-amber-900', 'alert-triangle'],
        'info' => ['border-blue-200 bg-blue-50 text-blue-800', 'info'],
    ];
    [$classes, $icon] = $styles[$type] ?? $styles['info'];
@endphp

<div
    x-data="{ visible: true }"
    x-show="visible"
    x-transition.opacity.duration.200ms
    @if ($autoDismiss) x-init="setTimeout(() => visible = false, 6000)" @endif
    role="{{ $type === 'error' ? 'alert' : 'status' }}"
    {{ $attributes->class(['flex items-start gap-3 rounded-md border px-4 py-3 text-sm shadow-sm', $classes]) }}
>
    <i data-lucide="{{ $icon }}" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"></i>
    <p class="min-w-0 flex-1 leading-6">{{ $message }}</p>
    <button type="button" @click="visible = false" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md transition hover:bg-black/5" aria-label="Fermer le message">
        <i data-lucide="x" class="h-4 w-4" aria-hidden="true"></i>
    </button>
</div>
