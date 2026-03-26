<template>
    <div class="p-6">
        <div class="flex items-center gap-4 mb-4">
            <h1 class="text-2xl font-bold text-yellow-400">Units</h1>
            <button
                @click="parseMap"
                :disabled="parsing || building"
                class="px-3 py-1 rounded text-sm font-medium transition-colors bg-gray-700 hover:bg-gray-600 text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ parsing ? 'Parsing...' : 'Parse Map' }}
            </button>
            <button
                @click="buildMap"
                :disabled="parsing || building"
                class="px-3 py-1 rounded text-sm font-medium transition-colors bg-gray-700 hover:bg-gray-600 text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ building ? 'Building...' : 'Build Map' }}
            </button>
            <span v-if="actionResult !== null" :class="actionResult.success ? 'text-green-400' : 'text-red-400'" class="text-sm">
                {{ actionResult.success ? 'Done' : 'Error' }}
            </span>
        </div>

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
                <table class="w-full text-xs text-left border-collapse">
                    <thead class="bg-gray-800 text-yellow-300 uppercase sticky top-0 z-20">
                        <tr>
                            <th class="sticky left-0 z-20 bg-gray-800 px-2 py-1 border border-gray-700 w-16 min-w-[4rem] whitespace-nowrap [box-shadow:2px_0_0_0_#ca8a04]">Unit</th>
                            <th class="sticky left-16 z-20 bg-gray-800 px-2 py-1 border border-gray-700 w-12 min-w-[3rem] text-center [box-shadow:2px_0_0_0_#ca8a04]">Icon</th>
                            <th
                                v-for="key in dynamicKeys"
                                :key="key"
                                class="px-2 py-1 border border-gray-700 min-w-[4rem] max-w-[8rem] break-words leading-tight font-medium"
                            >
                                {{ key }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(params, unitCode) in filteredUnits"
                            :key="unitCode"
                            class="hover:bg-gray-750 transition-colors"
                        >
                            <td class="sticky left-0 z-10 bg-gray-900 px-2 py-1 border border-gray-700/50 font-mono font-semibold text-yellow-200 whitespace-nowrap [box-shadow:2px_0_0_0_#ca8a04]">
                                {{ unitCode }}
                            </td>
                            <td class="sticky left-16 z-10 bg-gray-900 px-1 py-1 border border-gray-700/50 text-center [box-shadow:2px_0_0_0_#ca8a04]">
                                <img
                                    v-if="getCellValue(params, 'uico_png')"
                                    :src="'/storage/png/' + getCellValue(params, 'uico_png')"
                                    :alt="unitCode"
                                    class="w-10 h-10 object-contain mx-auto"
                                >
                            </td>
                            <td
                                v-for="key in dynamicKeys"
                                :key="key"
                                class="px-2 py-1 border border-gray-700/50 text-gray-300 break-words cursor-pointer select-none"
                                @dblclick="startEdit(unitCode, key, params)"
                            >
                                <template v-if="isEditing(unitCode, key)">
                                    <textarea
                                        v-model="editingValue"
                                        @blur="saveEdit(unitCode, key, params)"
                                        @keyup.escape="cancelEdit"
                                        @keydown.enter.exact.prevent="saveEdit(unitCode, key, params)"
                                        class="w-full bg-gray-900 border border-yellow-500 text-gray-100 px-1 py-0 text-xs rounded outline-none resize-y min-h-[3rem]"
                                        rows="3"
                                        v-focus
                                    />
                                </template>
                                <template v-else>
                                    {{ getCellValue(params, key) }}
                                </template>
                            </td>
                        </tr>
                        <tr v-if="!Object.keys(filteredUnits).length">
                            <td :colspan="dynamicKeys.length + 2" class="px-3 py-6 text-center text-gray-500 italic">
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

const vFocus = { mounted: (el) => el.focus() };

const loading = ref(true);
const parsing = ref(false);
const building = ref(false);
const actionResult = ref(null);
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

const dynamicKeys = computed(() => allKeys.value.filter(k => k !== 'uico_png'));

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

async function parseMap() {
    parsing.value = true;
    actionResult.value = null;
    try {
        const { data } = await axios.get('/parse-map');
        actionResult.value = data;
    } catch (e) {
        actionResult.value = { success: false, output: e?.response?.data?.message ?? 'Request failed' };
    } finally {
        parsing.value = false;
    }
}

async function buildMap() {
    building.value = true;
    actionResult.value = null;
    try {
        const { data } = await axios.get('/build-map');
        actionResult.value = data;
    } catch (e) {
        actionResult.value = { success: false, output: e?.response?.data?.message ?? 'Request failed' };
    } finally {
        building.value = false;
    }
}

const editingCell = ref(null); // { unitCode, key }
const editingValue = ref('');

function isEditing(unitCode, key) {
    return editingCell.value?.unitCode === unitCode && editingCell.value?.key === key;
}

function startEdit(unitCode, key, params) {
    editingCell.value = { unitCode, key };
    editingValue.value = String(getCellValue(params, key) ?? '');
}

function cancelEdit() {
    editingCell.value = null;
    editingValue.value = '';
}

async function saveEdit(unitCode, key, params) {
    if (!editingCell.value) return;

    const original = getCellValue(params, key);
    const newValue = editingValue.value;
    cancelEdit();

    if (newValue === String(original ?? '')) return;

    const param = params[key];
    const id = unitCode.split(':')[0];

    // Find db from any existing param of this unit
    const anyParam = param ?? Object.values(params).find(p => p && typeof p === 'object' && p.db);

    if (!anyParam) return;

    const fieldId = param?.id ?? key;
    const fieldType = param?.type ?? 'string';

    try {
        await axios.post('/update', { db: anyParam.db, id, key: fieldId, value: newValue, type: fieldType });
        if (param && typeof param === 'object') {
            param.value = newValue;
        } else {
            // Create local param entry so the cell shows the new value
            params[key] = { id: fieldId, db: anyParam.db, name: key, value: newValue, type: fieldType, level: 0, column: 0 };
        }
    } catch (e) {
        console.error('Failed to save', e);
    }
}

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
