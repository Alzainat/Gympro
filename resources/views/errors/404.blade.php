@php
    $dashboardUrl = auth()->user()?->profile?->role === 'trainer'
        ? url('/trainer')
        : url('/admin');
@endphp

<x-filament-panels::page.simple>

    <div class="mx-auto flex min-h-[70vh] max-w-2xl flex-col items-center justify-center px-6 text-center">

        <div
            class="mb-8 flex h-24 w-24 items-center justify-center rounded-3xl
                   bg-primary-50 ring-1 ring-primary-200
                   dark:bg-primary-500/10 dark:ring-primary-500/20"
        >
            <x-filament::icon
                icon="heroicon-o-map"
                class="h-12 w-12 text-primary-600 dark:text-primary-400"
            />
        </div>

        <p class="text-sm font-bold uppercase tracking-[0.3em] text-primary-600 dark:text-primary-400">
            Error 404
        </p>

        <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-5xl">
            Page Not Found
        </h1>

        <p class="mt-5 max-w-lg text-base leading-7 text-gray-600 dark:text-gray-400">
            The page you are trying to reach doesn’t exist,
            may have been moved, or the URL is incorrect.
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">

            <x-filament::button
                tag="a"
                href="{{ $dashboardUrl }}"
                icon="heroicon-m-home"
                size="lg"
                color="primary"
            >
                Dashboard
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
