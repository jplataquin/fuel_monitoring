<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-bold text-light mb-0">
                {{ __('Settings') }}
            </h2>
        </div>
    </x-slot>

    <div class="container-xl py-5">
        <div class="row g-4 justify-content-center">
            <!-- Users Card -->
            <div class="col-md-5">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100 overflow-hidden transition hover-shadow" style="transition: all 0.2s ease-in-out;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-4 d-inline-block mb-3 text-primary">
                                <!-- Users SVG Icon -->
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.109A12.018 12.018 0 0 1 12 21c-2.22 0-4.293-.601-6.075-1.65v-.109m12-1.08c1.218-.119 2.407-.291 3.564-.515M12 18H5.25m3.75-3.375a3.375 3.375 0 1 0 0-6.75 3.375 3.375 0 0 0 0 6.75Zm9.75-3c0 .822-.125 1.614-.356 2.361m.356-2.361a2.812 2.812 0 1 0-4.113-4.114c.231.747.356 1.539.356 2.361m0 0v1.44m-.113-4.114a2.811 2.811 0 0 0-3.921-.124M3.564 16.515C4.721 16.739 5.91 16.91 7.128 17.03m3.75-13.375a3.375 3.375 0 1 0-6.75 0 3.375 3.375 0 0 0 6.75 0Z"></path>
                                </svg>
                            </div>
                            <h3 class="h5 fw-bold text-light mb-2">Users</h3>
                            <p class="text-secondary small mb-4">
                                Create, view, edit and delete system users. Manage user roles (Administrators, Moderators, Budgeteers, Fuel Men, and Data Loggers).
                            </p>
                        </div>
                        <a href="{{ route('users.index') }}" class="btn btn-outline-primary rounded-pill w-100 fw-bold text-uppercase small py-2.5 cursor-pointer">
                            Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Classifications Card -->
            <div class="col-md-5">
                <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100 overflow-hidden transition hover-shadow" style="transition: all 0.2s ease-in-out;">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-4 d-inline-block mb-3 text-success">
                                <!-- Classifications/Tag SVG Icon -->
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581a1.125 1.125 0 0 0 1.59 0l7.581-7.581a1.125 1.125 0 0 0 0-1.59L12.82 3.659A2.25 2.25 0 0 0 11.23 3H9.568Zm0 0a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7.5h.008v.008H6V7.5Z"></path>
                                </svg>
                            </div>
                            <h3 class="h5 fw-bold text-light mb-2">Classifications</h3>
                            <p class="text-secondary small mb-4">
                                Manage asset classifications or categories (e.g., Heavy Equipment, Light Vehicles, Stationary Engines).
                            </p>
                        </div>
                        <a href="{{ route('asset-types.index') }}" class="btn btn-outline-success rounded-pill w-100 fw-bold text-uppercase small py-2.5 cursor-pointer">
                            Manage Classifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hover styles and cursor adjustments -->
    <style>
        .hover-shadow:hover {
            transform: translateY(-4px);
            border-color: rgba(111, 66, 193, 0.4) !important;
            box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.3) !important;
        }
        .cursor-pointer {
            cursor: pointer !important;
        }
    </style>
</x-app-layout>
