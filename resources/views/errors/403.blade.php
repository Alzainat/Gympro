@php
    $dashboardUrl = auth()->user()?->profile?->role === 'trainer'
        ? url('/trainer')
        : url('/admin');
@endphp

<x-filament-panels::page.simple>

    <div class="mx-auto flex min-h-[70vh] max-w-2xl flex-col items-center justify-center px-6 text-center">

        <div
            class="mb-8 flex h-24 w-24 items-center justify-center rounded-3xl
                   bg-danger-50 ring-1 ring-danger-200
                   dark:bg-danger-500/10 dark:ring-danger-500/20"
        >
            <x-filament::icon
                icon="heroicon-o-shield-exclamation"
                class="h-12 w-12 text-danger-600 dark:text-danger-400"
            />
        </div>

        <p class="text-sm font-bold uppercase tracking-[0.3em] text-danger-600 dark:text-danger-400">
            Error 403
        </p>

        <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-5xl">
            Unauthorized Access
        </h1>

        <p class="mt-5 max-w-lg text-base leading-7 text-gray-600 dark:text-gray-400">
            You don’t have permission to access this page.
            If you believe this is a mistake, please contact the administrator.
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">

            <x-filament::button
                tag="a"
                href="{{ $dashboardUrl }}"
                icon="heroicon-m-home"
                size="lg"
                color="danger"
            >
                Back to Dashboard
            </x-filament::button>

            <x-filament::button
                tag="a"
                href="{{ url()->previous() }}"
                icon="heroicon-m-arrow-uturn-left"
                size="lg"
                color="gray"
            >
                Go Back
            </x-filament::button>

        </div>

    </div>

</x-filament-panels::page.simple>
