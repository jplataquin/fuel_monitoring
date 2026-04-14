@props(['color' => 'primary', 'padding' => '!px-8 !py-3'])

@php
    $baseClasses = 'inline-flex items-center border border-transparent rounded-full font-bold text-xs uppercase tracking-widest transition ease-in-out duration-150 shadow-sm';
    
    $colors = [
        'primary' => 'bg-[#D0BCFF] !text-black hover:bg-[#EADDFF] focus:bg-[#EADDFF] active:bg-[#D0BCFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF]',
        'secondary' => 'bg-[#CCC2DC] !text-black hover:bg-[#E6E1E5] focus:outline-none focus:ring-2 focus:ring-[#CCC2DC]',
        'danger' => 'bg-[#F2B8B5] !text-black hover:bg-[#F9DEDC] focus:outline-none focus:ring-2 focus:ring-[#F2B8B5]',
        'info' => 'bg-[#A8EFF2] !text-black hover:bg-[#C1F5F7] focus:outline-none focus:ring-2 focus:ring-[#A8EFF2]',
        'indigo' => 'bg-indigo-600 !text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-lg',
        'indigo-light' => 'bg-indigo-200 !text-black hover:bg-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-200',
    ];

    $classes = $baseClasses . ' ' . $padding . ' ' . ($colors[$color] ?? $colors['primary']);
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
