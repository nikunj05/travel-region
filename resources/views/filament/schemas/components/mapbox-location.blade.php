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
        <div class="fi-input-wrp fi-fo-text-input">
            <div class="fi-input-wrp-content-ctn">
                <input
                    id="{{ $id }}"
                    type="text"
                    x-model="query"
                    @input.debounce.400ms="search"
                    placeholder="Search for location..."
                    class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                />
            </div>
        </div>

        <div
            x-show="isOpen"
            x-transition
            @click.away="isOpen = false"
            class="absolute z-50 mt-1 w-full"
        >
            <ul class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-lg shadow-lg max-h-60 overflow-auto">
                <template x-for="place in results" :key="place.id">
                    <li
                        @click="selectPlace(place)"
                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/5 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/5 last:border-b-0 transition"
                        x-text="place.place_name"
                    ></li>
                </template>

                <template x-if="results.length === 0 && query.length >= 3">
                    <li class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        No locations found
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('mapboxLocation', ({ value, statePath, token }) => ({
        query: value || '',
        results: [],
        isOpen: false,

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
                return;
            }

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
