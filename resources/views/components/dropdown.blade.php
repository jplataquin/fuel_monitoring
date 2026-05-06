@props(['align' => 'end', 'contentClasses' => ''])

@php
$alignmentClass = match ($align) {
    'start' => 'dropdown-menu-start',
    'end' => 'dropdown-menu-end',
    default => 'dropdown-menu-end',
};
@endphp

<div class="dropdown">
    <div data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
        {{ $trigger }}
    </div>

    <ul {{ $attributes->merge(['class' => "dropdown-menu {$alignmentClass} {$contentClasses}"]) }}>
        {{ $content }}
    </ul>
</div>
