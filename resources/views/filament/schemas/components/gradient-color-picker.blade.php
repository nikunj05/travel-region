@php
    $statePath = $getStatePath();
    $maxColors = $getMaxColors();
    $direction = $getDirection();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        rawValue: $wire.entangle('{{ $statePath }}').live,
        colors: [],
        direction: '{{ $direction }}',
        maxColors: {{ $maxColors }},
        finalCss: '',

        ensureArray(value) {
            if (Array.isArray(value)) return value;
            if (typeof value === 'string') {
                try {
                    const parsed = JSON.parse(value);
                    if (Array.isArray(parsed)) return parsed;
                } catch (_) {}
            }
            // default fallback
            return [
                { color: '#ff0000', position: 0 },
                { color: '#0000ff', position: 100 },
            ];
        },

        init() {
            this.$nextTick(() => {
                let value = this.rawValue;

                // unwrap Livewire proxy safely
                if (value && typeof value === 'object' && value.colors) {
                    this.colors = this.ensureArray(value.colors);
                    this.direction = value.direction || this.direction;
                } else {
                    // If the entangled value is just a JSON string
                    if (typeof value === 'string') {
                        try {
                            const parsed = JSON.parse(value);
                            if (parsed && parsed.colors) {
                                this.colors = this.ensureArray(parsed.colors);
                                this.direction = parsed.direction || this.direction;
                                this.finalCss = parsed.css || '';
                                return;
                            }
                        } catch (e) {
                            // ignore parsing error
                        }
                    }

                    // Fallback defaults
                    this.colors = this.ensureArray(value);
                }

                this.updateGradient(false);
            });
        },

        addColor() {
            if (this.colors.length < this.maxColors) {
                this.colors.push({ color: '#000000', position: 50 });
                this.updateGradient();
            }
        },

        removeColor(index) {
            if (this.colors.length > 2) {
                this.colors.splice(index, 1);
                this.updateGradient();
            }
        },

        getGradient() {
            const arr = this.ensureArray(this.colors);
            const sortedColors = [...arr].sort((a, b) => a.position - b.position);
            const colorStops = sortedColors.map(c => `${c.color} ${c.position}%`).join(', ');
            return `linear-gradient(${this.direction}, ${colorStops})`;
        },

        updateGradient(sync = true) {
            this.finalCss = this.getGradient();

            if (sync) {
                this.rawValue = {
                    colors: this.colors,
                    direction: this.direction,
                    css: this.finalCss,
                };
            }
        }
    }" x-init="init" class="space-y-4">
        <!-- Preview -->
        <div class="w-full h-24 rounded-lg border border-gray-300 dark:border-gray-600" :style="{ background: finalCss }"
            style="width: 100%; height: 250px; margin-bottom: 20px"></div>

        <!-- Direction Selector -->
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Direction
            </label>
            <select x-model="direction" @change="updateGradient()"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
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
                <button type="button" @click="addColor()" x-show="colors.length < maxColors"
                    class="fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300
                    dark:fi-bg-color-600 dark:hover:fi-bg-color-500
                    fi-text-color-900 hover:fi-text-color-800
                    dark:fi-text-color-950 dark:hover:fi-text-color-950
                    fi-btn fi-size-md fi-ac-btn-action">
                    + Add Color
                </button>
            </div>

            <template x-for="(color, index) in colors" :key="index">
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1">
                        <label class="text-xs text-gray-600 dark:text-gray-400">
                            Position: <span x-text="color.position + '%'"></span>
                        </label>
                        <input type="color" x-model="color.color" @input="updateGradient()"
                            class="w-12 h-12 rounded cursor-pointer" />
                        <input type="range" x-model.number="color.position" min="0" max="100"
                            @input="updateGradient()" class="w-full" />
                    </div>

                    <div class="fi-input-wrp fi-fo-text-input">
                        <div class="fi-input-wrp-content-ctn">
                            <input type="text" readonly x-model="color.color"
                                class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                                placeholder="#000000" />
                        </div>
                        <button type="button" @click="removeColor(index)" x-show="colors.length > 2"
                            class="fi-color fi-color-primary fi-bg-color-400 hover:fi-bg-color-300
                                dark:fi-bg-color-600 dark:hover:fi-bg-color-500
                                fi-text-color-900 hover:fi-text-color-800
                                dark:fi-text-color-950 dark:hover:fi-text-color-950
                                fi-btn fi-size-md fi-ac-btn-action">
                            Remove
                        </button>
                    </div>
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
                    <input type="text" :value="finalCss" readonly
                        class="fi-input fi-fo-text-input fi-fo-input-without-prefix-suffix w-full"
                        @click="$event.target.select()" />
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
