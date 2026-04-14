@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#D0BCFF] text-start text-base font-medium text-[#D0BCFF] bg-[#49454F] focus:outline-none focus:text-[#D0BCFF] focus:bg-[#49454F] focus:border-[#D0BCFF] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-[#CAC4D0] hover:text-[#E6E1E5] hover:bg-[#49454F] hover:border-[#D0BCFF] focus:outline-none focus:text-[#E6E1E5] focus:bg-[#49454F] focus:border-[#D0BCFF] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
