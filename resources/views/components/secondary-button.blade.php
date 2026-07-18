<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex min-h-10 items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-[#8c6cf0] hover:text-[#673de6] focus:outline-none focus:ring-2 focus:ring-[#8062e8] focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
