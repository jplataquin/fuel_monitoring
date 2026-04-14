@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#D0BCFF] text-sm font-bold uppercase tracking-widest leading-5 text-[#E6E1E5] focus:outline-none focus:border-[#D0BCFF] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-bold uppercase tracking-widest leading-5 text-[#CAC4D0] hover:text-[#E6E1E5] hover:border-[#D0BCFF] focus:outline-none focus:text-[#E6E1E5] focus:border-[#D0BCFF] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
