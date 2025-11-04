@php
    $statePath = $getStatePath();
    $maxColors = $getMaxColors();
    $direction = $getDirection();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            colors: $wire.entangle('{{ $statePath }}').live,
            direction: '{{ $direction }}',
            maxColors: {{ $maxColors }},

            init() {
                try {
                    if (typeof this.colors === 'string') {
                        this.colors = JSON.parse(this.colors);
                    }
                } catch (_) {}

                if (!Array.isArray(this.colors) || this.colors.length === 0) {
                    this.colors = [
                        { color: '#ff0000', position: 0 },
                        { color: '#0000ff', position: 100 }
                    ];
                }
            },

            addColor() {
                if (this.colors.length < this.maxColors) {
                    this.colors.push({ color: '#000000', position: 50 });
                    this.colors = [...this.colors];
                }
            },

            removeColor(index) {
                if (this.colors.length > 2) {
                    this.colors.splice(index, 1);
                    this.colors = [...this.colors];
                }
            },

            getGradient() {
                const sortedColors = [...this.colors].sort((a, b) => a.position - b.position);
                const colorStops = sortedColors.map(c => `${c.color} ${c.position}%`).join(', ');
                return `linear-gradient(${this.direction}, ${colorStops})`;
            }
        }"
        class="space-y-4"
    >
        <!-- Preview -->
        <div
            class="w-full h-24 rounded-lg border border-gray-300 dark:border-gray-600"
            :style="{ background: getGradient() }"
        ></div>

        <!-- Direction Selector -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Direction
            </label>
            <select
                x-model="direction"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option value="to right">Left to Right</option>
                <option value="to left">Right to Left</option>
                <option value="to bottom">Top to Bottom</option>
                <option value="to top">Bottom to Top</option>
                <option value="to bottom right">Diagonal ↘</option>
                <option value="to bottom left">Diagonal ↙</option>
                <option value="to top right">Diagonal ↗</option>
                <option value="to top left">Diagonal ↖</option>
            </select>
        </div>

        <!-- Color Stops -->
        <div class="space-y-3">
            <div class="fi-ac fi-align-start">
                <button
                    type="button"
                    @click="addColor()"
                    x-show="colors.length < maxColors"
                    class="fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300 dark:fi-bg-color-600 dark:hover:fi-bg-color-500 fi-text-color-900 hover:fi-text-color-800 dark:fi-text-color-950 dark:hover:fi-text-color-950 fi-btn fi-size-md fi-ac-btn-action"
                >
                    + Add Color
                </button>
            </div>

            <template x-for="(color, index) in colors" :key="index">
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <input
                        type="color"
                        x-model="color.color"
                        class="w-12 h-12 rounded cursor-pointer"
                    />

                    <div class="flex-1">
                        <label class="text-xs text-gray-600 dark:text-gray-400">
                            Position: <span x-text="color.position + '%'"></span>
                        </label>
                        <input
                            type="range"
                            x-model.number="color.position"
                            min="0"
                            max="100"
                            class="w-full"
                        />
                    </div>

                    <div class="fi-input-wrp fi-fo-text-input">
                        <div class="fi-input-wrp-content-ctn">
                            <input
                                type="text"
                                readonly
                                x-model="color.color"
                                class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                                placeholder="#000000"
                            />
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="removeColor(index)"
                        x-show="colors.length > 2"
                        class="text-red-600 hover:text-red-700 dark:text-red-400"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <!-- CSS Output -->
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                CSS Output
            </label>
            <div class="fi-input-wrp fi-fo-text-input">
                <div class="fi-input-wrp-content-ctn">
                    <input
                        type="text"
                        :value="getGradient()"
                        readonly
                        class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                        @click="$event.target.select()"
                    />
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
