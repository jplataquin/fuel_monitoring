@props([
    'name',
    'show' => false,
    'maxWidth' => 'md'
])

@php
$maxWidthClass = [
    'sm' => 'modal-sm',
    'md' => '',
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => 'modal-xl',
][$maxWidth] ?? '';
@endphp

<div
    x-data="{
        show: @js($show),
        modal: null,
        init() {
            this.modal = new bootstrap.Modal(this.$el);
            this.$watch('show', value => {
                if (value) {
                    this.modal.show();
                } else {
                    this.modal.hide();
                }
            });
        }
    }"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:hidden.bs.modal="show = false"
    class="modal fade"
    id="{{ $name }}"
    tabindex="-1"
    aria-labelledby="{{ $name }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog {{ $maxWidthClass }} modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
