@props(['active'])

@php
$classes = ($active ?? false)
            ? 'nav-link active fw-bold text-uppercase'
            : 'nav-link fw-bold text-uppercase';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
