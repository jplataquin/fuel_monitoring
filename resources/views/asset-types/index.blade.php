<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Classifications') }}
            </h2>
            <a href="{{ route('asset-types.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                <svg class="me-2" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add Classification') }}
            </a>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr class="bg-secondary bg-opacity-10">
                                    <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest">Classifications</th>
                                    <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold tracking-widest text-end" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle">
                                @foreach($assetTypes as $type)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="fw-bold text-light">{{ $type->name }}</span>
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <a href="{{ route('asset-types.edit', $type) }}" class="btn btn-link text-primary p-2 rounded-circle hover-bg-light hover-bg-opacity-10" title="Edit">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                                </a>
                                                <form action="{{ route('asset-types.destroy', $type) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-link text-danger p-2 rounded-circle hover-bg-light hover-bg-opacity-10" onclick="return confirm('Are you sure?')" title="Delete">
                                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
