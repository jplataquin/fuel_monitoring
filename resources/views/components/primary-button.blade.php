<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary rounded-pill px-4 py-2 fw-bold text-uppercase tracking-widest shadow-sm']) }}>
    {{ $slot }}
</button>
