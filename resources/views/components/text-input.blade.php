@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-[#1C1B1F] border-[#49454F]/50 focus:border-[#D0BCFF] focus:ring-[#D0BCFF] rounded-xl shadow-sm text-[#E6E1E5] placeholder-[#CAC4D0]/50']) }}>
