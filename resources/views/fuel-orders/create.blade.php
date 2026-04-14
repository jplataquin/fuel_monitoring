<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
            {{ __('Create Fuel Order') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] shadow-2xl p-8 md:p-12 border border-[#49454F]/50">
                <div class="mb-8 border-b border-[#49454F]/50 pb-6">
                    <h3 class="text-xl font-bold text-[#D0BCFF]">Issue Fuel Order</h3>
                    <p class="text-[#CAC4D0] mt-2 text-sm">Calculate fuel consumption and issue an order to replenish the asset's fuel tank.</p>
                </div>

                @livewire('create-fuel-order')
            </div>
        </div>
    </div>
</x-app-layout>
