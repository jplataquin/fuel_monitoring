<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#E6E1E5] tracking-tight">
                {{ __('Classifications') }}
            </h2>
            <x-button-link :href="route('asset-types.create')" color="primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add Classification') }}
            </x-button-link>
        </div>
    </x-slot>

    <div class="lg:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-[#1C1B1F] rounded-[28px] overflow-hidden border border-[#49454F]/50 shadow-xl">
                <div class="p-0 text-gray-100">
                    <table class="min-w-full divide-y divide-[#49454F]/50">
                        <thead>
                            <tr class="bg-[#49454F]/10">
                                <th class="lg:px-8 lg:py-5 text-left text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em]">Classifications</th>
                                <th class="lg:px-8 lg:py-5 text-right text-xs font-bold text-[#CAC4D0] uppercase tracking-[0.2em] w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#49454F]/30 bg-[#1C1B1F]">
                            @foreach($assetTypes as $type)
                                <tr class="hover:bg-[#49454F]/10 transition-colors">
                                    <td class="lg:px-8 lg:py-5 whitespace-nowrap">
                                        <span class="text-base font-bold text-[#E6E1E5] tracking-tight">{{ $type->name }}</span>
                                    </td>
                                    <td class="lg:px-8 lg:py-5 whitespace-nowrap text-right space-x-1">
                                        <a href="{{ route('asset-types.edit', $type) }}" class="text-[#D0BCFF] hover:bg-[#D0BCFF]/10 p-2.5 rounded-full transition-colors inline-flex items-center" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                        <form action="{{ route('asset-types.destroy', $type) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[#F2B8B5] hover:bg-[#F2B8B5]/10 p-2.5 rounded-full transition-colors inline-flex items-center" onclick="return confirm('Are you sure?')" title="Delete">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
