<template>
    <div class="min-h-screen bg-gray-900 text-gray-100 p-6">
        <h1 class="text-2xl font-bold text-yellow-400 mb-4">⚔️ Units</h1>

        <!-- Tag Filters -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a
                href="/units"
                class="px-3 py-1 rounded text-sm font-medium transition-colors"
                :class="!activeLegend ? 'bg-yellow-500 text-gray-900' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'"
            >
                All
            </a>
            <a
                v-for="tag in tags"
                :key="tag"
                :href="'?legends=' + encodeURIComponent(tag.toLowerCase())"
                class="px-3 py-1 rounded text-sm font-medium transition-colors"
                :class="activeLegend === tag.toLowerCase() ? 'bg-yellow-500 text-gray-900' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'"
            >
                {{ tag }}
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-lg border border-gray-700">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-800 text-yellow-300 text-xs uppercase">
                    <tr>
                        <th class="px-3 py-2 border-b border-gray-700 whitespace-nowrap">Unit</th>
                        <th
                            v-for="key in allKeys"
                            :key="key"
                            class="px-3 py-2 border-b border-gray-700 whitespace-nowrap"
                        >
                            {{ key }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(params, unitCode) in units"
                        :key="unitCode"
                        class="border-b border-gray-800 hover:bg-gray-800 transition-colors"
                    >
                        <td class="px-3 py-2 font-mono font-semibold text-yellow-200 whitespace-nowrap">
                            {{ unitCode }}
                        </td>
                        <td
                            v-for="key in allKeys"
                            :key="key"
                            class="px-3 py-2 text-gray-300 max-w-xs truncate"
                        >
                            <template v-if="key === 'uico_png' && getCellValue(params, key)">
                                <img
                                    :src="'/storage/png/' + getCellValue(params, key)"
                                    :alt="unitCode"
                                    class="w-12 h-12 object-contain"
                                >
                            </template>
                            <template v-else>
                                {{ getCellValue(params, key) }}
                            </template>
                        </td>
                    </tr>
                    <tr v-if="!Object.keys(units).length">
                        <td :colspan="allKeys.length + 1" class="px-3 py-6 text-center text-gray-500 italic">
                            No units found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    units: { type: Object, required: true },
    tags: { type: Array, default: () => [] },
    allKeys: { type: Array, default: () => [] },
});

const activeLegend = computed(() => {
    const params = new URLSearchParams(window.location.search);
    return params.get('legends') ?? null;
});

function getCellValue(params, key) {
    const param = params[key];
    if (param === null || param === undefined) return null;
    if (typeof param === 'object' && 'value' in param) return param.value;
    return param;
}
</script>
