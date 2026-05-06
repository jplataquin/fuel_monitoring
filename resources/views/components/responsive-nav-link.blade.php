@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link active fw-bold border-start border-primary border-4 ps-3 bg-primary bg-opacity-10'
            : 'nav-link ps-3 border-start border-transparent border-4';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
