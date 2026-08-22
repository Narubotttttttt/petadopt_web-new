@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-[#12272b] text-gray-800 dark:text-white shadow-sm focus:border-[#199CA4] focus:ring-4 focus:ring-[#199CA4]/10 focus:outline-none transition duration-150']) }}>