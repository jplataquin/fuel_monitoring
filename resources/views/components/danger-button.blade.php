<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-danger rounded-pill fw-bold text-uppercase px-4 py-2']) }}>
    {{ $slot }}
</button>
