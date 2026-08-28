<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-bold text-light mb-0">
            {{ __('Create Fuel Order') }}
        </h2>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card bg-dark border border-secondary border-opacity-25 rounded-2 overflow-hidden">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 border-bottom border-secondary border-opacity-25 pb-4">
                            <h3 class="h5 fw-bold text-primary mb-2">Issue Fuel Order</h3>
                            <p class="text-secondary mb-0 small">Calculate fuel consumption and issue an order to replenish the asset's fuel tank.</p>
                        </div>

                        @livewire('create-fuel-order')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
