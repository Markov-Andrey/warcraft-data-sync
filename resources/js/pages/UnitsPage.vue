<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold text-yellow-400 mb-4">Units</h1>

        <!-- Loading -->
        <div v-if="loading" class="text-gray-400 text-sm">Loading units...</div>

        <template v-else>
            <!-- Tag Filters -->
            <div class="flex flex-wrap gap-2 mb-6">
                <button
                    @click="setLegend(null)"
                    class="px-3 py-1 rounded text-sm font-medium transition-colors"
                    :class="!activeLegend ? 'bg-yellow-500 text-gray-900' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'"
                >
                    All
                </button>
                <button
                    v-for="tag in tags"
                    :key="tag"
                    @click="setLegend(tag)"
                    class="px-3 py-1 rounded text-sm font-medium transition-colors"
                    :class="activeLegend === tag ? 'bg-yellow-500 text-gray-900' : 'bg-gray-700 hover:bg-gray-600 text-gray-200'"
                >
                    {{ tag }}
                </button>
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
                            v-for="(params, unitCode) in filteredUnits"
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
                        <tr v-if="!Object.keys(filteredUnits).length">
                            <td :colspan="allKeys.length + 1" class="px-3 py-6 text-center text-gray-500 italic">
                                No units found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const loading = ref(true);
const units = ref({});
const tags = ref([]);
const allKeys = ref([]);
const activeLegend = ref(null);

onMounted(async () => {
    try {
        const { data } = await axios.get('/units');
        units.value = data.units;
        tags.value = data.tags;
        allKeys.value = data.allKeys;
    } catch {
        console.error('Failed to load units');
    } finally {
        loading.value = false;
    }
});

const filteredUnits = computed(() => {
    if (!activeLegend.value) return units.value;

    const needle = '[' + activeLegend.value.toLowerCase() + ']';
    return Object.fromEntries(
        Object.entries(units.value).filter(([, params]) => {
            const name = String(getCellValue(params, 'Text - Name') ?? '').toLowerCase();
            return name.includes(needle);
        })
    );
});

function setLegend(tag) {
    activeLegend.value = tag;
}

function getCellValue(params, key) {
    const param = params[key];
    if (param === null || param === undefined) return null;
    if (typeof param === 'object' && 'value' in param) return param.value;
    return param;
}
</script>
