<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Fuel Order #') }}{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}
            </h2>
            <a href="{{ route('fuel-orders.index') }}" class="inline-flex items-center px-4 py-2 bg-[#49454F] border border-transparent rounded-full font-bold text-xs text-[#E6E1E5] uppercase tracking-widest hover:bg-[#CAC4D0] hover:text-[#1C1B1F] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Orders
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
           
                <form method="POST" action="{{ route('fuel-orders.store-actualization', $fuelOrder) }}">

                 <div class="bg-white rounded-[28px] shadow-2xl border border-gray-100 p-10 md:p-14">
                    @csrf
                    
                    <div class="text-center mb-10 pb-10 border-b-2 border-gray-200">
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Actualize Fuel Order</h1>
                        <p class="text-gray-500 mt-2 font-medium">Order Number: #{{ str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT) }}</p>
                        <div class="mt-4 flex justify-center items-center gap-3 p-4 bg-[#2D2930] rounded-2xl inline-flex border border-[#49454F]">
                            <label class="text-[#CAC4D0] font-bold text-lg uppercase tracking-wider">Status:</label>
                            <div class="relative">
                                <div class="w-64 rounded-xl border-2 border-[#D0BCFF]/50 bg-[#D0BCFF]/10 shadow-inner font-black text-[#D0BCFF] px-6 py-3 text-xl opacity-80 cursor-not-allowed">
                                    {{ $fuelOrder->status }}
                                </div>
                            </div>
                        </div>
                    </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-8 mb-12">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Asset Details</h4>
                        <p class="text-xl font-bold text-gray-900">{{ $fuelOrder->asset->fleet_no }}</p>
                        <p class="text-sm text-gray-600">{{ $fuelOrder->asset->assetType->name ?? 'N/A' }} | {{ $fuelOrder->asset->plate_no ?? 'No Plate' }}</p>
                    </div>
                    <div class="flex space-x-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Date Range</h4>
                            <p class="text-lg font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($fuelOrder->date_from)->format('M d, Y') }} 
                                - 
                                {{ \Carbon\Carbon::parse($fuelOrder->date_to)->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-12 gap-y-8 mb-12">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Calculation Method</h4>
                        <p class="text-lg font-medium text-gray-900 capitalize">{{ $fuelOrder->utilizationEntries->first()?->calculation_type ?? 'N/A' }}</p>
                    </div>
                    <div class="flex space-x-8">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">KM Factor</h4>
                            <p class="text-lg font-bold text-indigo-600">{{ number_format($fuelOrder->fuel_factor_km, 2) }} KM/L</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">HR Factor</h4>
                            <p class="text-lg font-bold text-indigo-600">{{ number_format($fuelOrder->fuel_factor_hr, 2) }} L/HR</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6 mb-12">
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Calculated KM</h4>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($fuelOrder->calculated_kilometers, 2) }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Total Calculated Hours</h4>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($fuelOrder->calculated_hours, 2) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100 flex flex-col justify-center">
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider mb-2">Consumed Fuel (Liters)</h4>
                        <p class="text-2xl font-black text-indigo-700">{{ number_format($fuelOrder->calculated_quantity, 2) }}</p>
                    </div>
                </div>

                <div class="bg-gray-900 rounded-2xl p-8 mb-12">
                    <div class="mb-6">
                        <label for="say_quantity" class="block text-gray-300 font-bold text-lg mb-2">Say Fuel Quantity (Approved)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="say_quantity" id="say_quantity" value="{{ old('say_quantity', $fuelOrder->say_quantity) }}" class="mt-1 block w-full rounded-xl border-gray-600 bg-gray-700 text-indigo-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-2xl font-black px-4 py-3 font-mono pr-12 cursor-not-allowed opacity-80" readonly placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-indigo-300 font-black text-xl">L</span>
                            </div>
                        </div>
                        @error('say_quantity')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-700 pt-6">
                        <label for="actual_quantity" class="block text-gray-300 font-bold text-lg mb-2">Actual Quantity Dispensed (Liters) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="actual_quantity" id="actual_quantity" value="{{ old('actual_quantity', $fuelOrder->actual_quantity) }}" class="mt-1 block w-full rounded-xl border-gray-600 bg-gray-800 text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-lg px-4 py-3 font-mono" required placeholder="0.00">
                        @error('actual_quantity')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
        </div>
                <div class="flex justify-end gap-4 mt-12 pt-8 border-t border-gray-200">
                    <a href="{{ route('fuel-orders.index') }}" class="inline-flex items-center px-6 py-3 bg-[#49454F]/50 border border-transparent rounded-full font-black text-sm text-[#E6E1E5] uppercase tracking-widest hover:bg-[#49454F] focus:outline-none focus:ring-2 focus:ring-[#49454F] focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-8 py-3 bg-[#D0BCFF] border border-transparent rounded-full font-black text-sm text-[#381E72] uppercase tracking-widest shadow-md hover:bg-[#EADDFF] focus:bg-[#EADDFF] active:bg-[#D0BCFF] focus:outline-none focus:ring-2 focus:ring-[#D0BCFF] focus:ring-offset-2 transition ease-in-out duration-150 cursor-pointer">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Actualize
                    </button>
                </div>
                </form>

        </div>
    </div>
</x-app-layout>
