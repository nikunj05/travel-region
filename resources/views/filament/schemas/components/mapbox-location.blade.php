@php
    $id = $getId();
    $statePath = $getStatePath();
    $label = $getLabel();
    $value = $getState();
@endphp

<div class="filament-forms-field-wrapper">
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

    <div
        x-data="googlePlacesLocation({
            value: @js($value),
            statePath: @js($statePath),
            fieldId: @js($id)
        })"
        x-init="init()"
        wire:ignore.self
        class="relative"
    >
        <div class="fi-input-wrp fi-fo-text-input">
            <div class="fi-input-wrp-content-ctn">
                <input
                    :id="fieldId"
                    type="text"
                    x-model="query"
                    @input.debounce.400ms="search"
                    @focus="handleFocus"
                    placeholder="Search for location..."
                    class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                    autocomplete="off"
                />
            </div>
        </div>

        <div
            x-show="isOpen"
            x-transition
            @click.away="closeDropdown"
            class="absolute z-50 mt-1 w-full"
            style="display: none;"
        >
            <ul class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-lg shadow-lg max-h-60 overflow-auto">
                <template x-for="(place, index) in results" :key="place.place_id || index">
                    <li
                        @click="selectPlace(place)"
                        class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-white/5 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/5 last:border-b-0 transition"
                        x-text="place.formatted_address || place.name"
                    ></li>
                </template>

                <template x-if="results.length === 0 && query.length >= 3 && !isLoading">
                    <li class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        No locations found
                    </li>
                </template>

                <template x-if="isLoading">
                    <li class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                        Searching...
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>

@script
<script>
    Alpine.data('googlePlacesLocation', ({ value, statePath, fieldId }) => ({
        query: value || '',
        results: [],
        isOpen: false,
        isLoading: false,
        fieldId: fieldId,

        init() {
            // Initialize with current value
            if (value) {
                this.query = value;
            }

            // Watch for query changes
            this.$watch('query', (newValue) => {
                if (!newValue || newValue.length === 0) {
                    this.results = [];
                    this.isOpen = false;
                }
            });
        },

        handleFocus() {
            // Show dropdown if we have results
            if (this.results.length > 0) {
                this.isOpen = true;
            }
        },

        closeDropdown() {
            this.isOpen = false;
        },

        async search() {
            if (this.query.length < 3) {
                this.results = [];
                this.isOpen = false;
                return;
            }

            this.isLoading = true;

            try {
                const res = await fetch(
                    `/places/search?query=${encodeURIComponent(this.query)}`
                );

                if (!res.ok) {
                    throw new Error('Failed to fetch places');
                }

                const data = await res.json();
                this.results = data.results || [];
                this.isOpen = this.results.length > 0;
            } catch (error) {
                console.error('Places search error:', error);
                this.results = [];
                this.isOpen = false;
            } finally {
                this.isLoading = false;
            }
        },

        selectPlace(place) {
            // Set the display text
            this.query = place.formatted_address || place.name;
            this.results = [];
            this.isOpen = false;

            // Parse the state path to get base path
            const pathParts = statePath.split('.');
            const basePath = pathParts.slice(0, -1).join('.');

            // Get coordinates
            const longitude = place.geometry?.location?.lng;
            const latitude = place.geometry?.location?.lat;

            // Parse address components
            const addressComponents = place.address_components || [];

            const getComponent = (types) => {
                const component = addressComponents.find(c =>
                    types.some(type => c.types.includes(type))
                );
                return component ? component.long_name : '';
            };

            // Extract location details
            const city = getComponent(['locality', 'postal_town', 'administrative_area_level_2']);
            const state = getComponent(['administrative_area_level_1']);
            const country = getComponent(['country']);

            // Update Livewire state using $wire
            try {
                // Update main field
                this.$wire.set(statePath, this.query);

                // Update related fields if base path exists
                if (basePath && latitude && longitude) {
                    this.$wire.set(`${basePath}.latitude`, latitude);
                    this.$wire.set(`${basePath}.longitude`, longitude);
                    this.$wire.set(`${basePath}.city`, city);
                    this.$wire.set(`${basePath}.state`, state);
                    this.$wire.set(`${basePath}.country`, country);
                }
            } catch (error) {
                console.error('Error updating Livewire state:', error);
            }
        }
    }));
</script>
@endscript
