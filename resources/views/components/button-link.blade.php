@props(['color' => 'primary'])

@php
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'danger' => 'btn-danger',
        'info' => 'btn-info',
        'indigo' => 'btn-primary',
        'indigo-light' => 'btn-light',
    ];

    $variant = $variants[$color] ?? 'btn-primary';
    $classes = "btn {$variant} rounded-pill fw-bold text-uppercase";
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
