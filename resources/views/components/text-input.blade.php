@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-md border-slate-300 shadow-sm focus:border-[#8062e8] focus:ring-[#8062e8]']) }}>
