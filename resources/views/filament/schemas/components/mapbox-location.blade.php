@php
    $id = $getId();
    $statePath = $getStatePath();
    $label = $getLabel();
    $value = $getState();
@endphp

<div
    x-data="mapboxLocation({
        value: @js($value),
        statePath: '{{ $statePath }}',
        token: '{{ config('services.mapbox.token') ?? env('MAPBOX_TOKEN') }}'
    })"
    x-init="init()"
    class="filament-forms-field-wrapper"
    wire:ignore
>
    @if ($label)
        <label for="{{ $id }}" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3 mb-2">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ $label }}
                @if ($isRequired())
                    <sup class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                @endif
            </span>
        </label>
    @endif

    <div class="relative">
        <input
            id="{{ $id }}"
            type="text"
            x-model="query"
            @input.debounce.400ms="search"
            placeholder="Search for location..."
            class="fi-input block w-full border-gray-300 shadow-sm rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-primary-600 focus:ring-primary-600 dark:focus:border-primary-500"
        />

        <div
            x-show="isOpen"
            x-transition
            @click.away="isOpen = false"
            class="absolute z-50 mt-1 w-full"
        >
            <ul class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-auto">
                <template x-for="place in results" :key="place.id">
                    <li
                        @click="selectPlace(place)"
                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 text-sm text-gray-900 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                        x-text="place.place_name"
                    ></li>
                </template>

                <template x-if="results.length === 0 && query.length >= 3 && !loading">
                    <li class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        No locations found
                    </li>
                </template>
            </ul>
        </div>

        <div x-show="loading" class="absolute right-3 top-3">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('mapboxLocation', ({ value, statePath, token }) => ({
        query: value || '',
        results: [],
        isOpen: false,
        loading: false,

        init() {
            // Watch for external state changes
            this.$watch('query', (newValue) => {
                if (!newValue) {
                    this.results = [];
                    this.isOpen = false;
                }
            });
        },

        async search() {
            if (this.query.length < 3) {
                this.results = [];
                this.isOpen = false;
                this.loading = false;
                return;
            }

            this.loading = true;

            try {
                const res = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(this.query)}.json?access_token=${token}&autocomplete=true&limit=5&types=place,locality,neighborhood,address`
                );
                const data = await res.json();
                this.results = data.features || [];
                this.isOpen = this.results.length > 0;
            } catch (error) {
                console.error('Mapbox search error:', error);
                this.results = [];
                this.isOpen = false;
            } finally {
                this.loading = false;
            }
        },

        selectPlace(place) {
            this.query = place.place_name;
            this.results = [];
            this.isOpen = false;

            // Extract the base path (everything except the last segment)
            const pathParts = statePath.split('.');
            const basePath = pathParts.slice(0, -1).join('.');

            // Get coordinates
            const longitude = place.geometry.coordinates[0];
            const latitude = place.geometry.coordinates[1];

            // Parse location components
            const context = place.context || [];
            const getContextValue = (types) => {
                const match = context.find(c => types.some(t => c.id.startsWith(t)));
                return match ? match.text : '';
            };

            // Determine city
            let city = '';
            if (place.place_type.includes('place')) {
                city = place.text;
            } else if (place.place_type.includes('locality')) {
                city = getContextValue(['place']) || place.text;
            } else {
                city = getContextValue(['place', 'locality']) || place.text;
            }

            const state = getContextValue(['region']);
            const country = getContextValue(['country']);

            // Use $wire to update all fields at once
            this.$wire.set(statePath, place.place_name);

            // Also update individual fields to ensure they're set
            if (basePath) {
                this.$wire.set(`${basePath}.latitude`, latitude);
                this.$wire.set(`${basePath}.longitude`, longitude);
                this.$wire.set(`${basePath}.city`, city);
                this.$wire.set(`${basePath}.state`, state);
                this.$wire.set(`${basePath}.country`, country);
            }
        }
    }));
</script>
@endscript
