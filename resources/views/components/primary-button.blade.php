<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-10 items-center justify-center rounded-md bg-[#673de6] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5530c9] focus:outline-none focus:ring-2 focus:ring-[#8062e8] focus:ring-offset-2 disabled:opacity-60']) }}>
    {{ $slot }}
</button>
