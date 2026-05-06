@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control bg-dark text-light border-secondary border-opacity-50 focus:border-primary focus:ring-1 focus:ring-primary shadow-sm']) }}>
