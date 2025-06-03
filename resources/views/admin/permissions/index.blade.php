<x-app-layout>
    <x-slot name="header"><h2 class="h4 fw-semibold text-dark">{{ __('Elenco Permessi di Sistema') }}</h2></x-slot>
    <div class="py-5">
        <div class="container">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>ID</th><th>{{ __('Nome Permesso') }}</th><th>{{ __('Guard') }}</th></tr></thead>
                            <tbody>
                                @forelse ($permissions as $permission)
                                    <tr><td>{{ $permission->id }}</td><td>{{ $permission->name }}</td><td>{{ $permission->guard_name }}</td></tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">{{ __('Nessun permesso definito.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $permissions->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>