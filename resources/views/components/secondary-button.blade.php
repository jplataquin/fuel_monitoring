<button {{ $attributes->merge(['type' => 'button', 'class' => 'btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold text-uppercase tracking-widest shadow-sm']) }}>
    {{ $slot }}
</button>
