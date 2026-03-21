<template>
    <div class="min-h-screen bg-gray-900 text-gray-100 p-6">
        <h1 class="text-2xl font-bold text-yellow-400 mb-6">⚔️ WarCraft Data Sync</h1>

        <!-- Info + Projects -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Info Panel -->
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <h2 class="text-base font-semibold text-yellow-300 mb-3">Info</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 shrink-0">Current project:</span>
                        <select
                            v-model="currentProject"
                            @change="switchProject"
                            class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white flex-1 min-w-0"
                        >
                            <option value="" disabled>-</option>
                            <option v-for="(project, key) in childProjects" :key="key" :value="key">
                                {{ project.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400">Last sync:</span>
                        <span>{{ configInfo.last_synced || '—' }}</span>
                    </div>
                    <div class="flex gap-2">
                        <span class="text-gray-400">Last build:</span>
                        <span>{{ configInfo.last_build || '—' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 shrink-0">Build version:</span>
                        <input
                            v-model="buildVersion"
                            @change="updateVersion"
                            class="bg-gray-700 border border-gray-600 rounded px-2 py-1 text-white w-24"
                        >
                    </div>
                </div>
            </div>

            <!-- Child Projects -->
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <h2 class="text-base font-semibold text-yellow-300 mb-3">Child Projects</h2>
                <div class="space-y-1">
                    <div
                        v-for="(project, key) in childProjects"
                        :key="key"
                        class="flex items-center gap-2 text-sm py-0.5"
                        :class="key === currentProject ? 'text-green-400' : 'text-gray-300'"
                    >
                        <span>{{ key === currentProject ? '✅' : '⬜' }}</span>
                        <span>{{ project.name }} <span class="text-gray-500">({{ key }})</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 mb-4">
            <button
                @click="syncToChild"
                :disabled="!!loading"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg font-medium text-sm transition-colors"
            >
                {{ loading === 'sync' ? '⏳ Syncing...' : '🔄 Sync to Child' }}
            </button>
            <button
                @click="compileBuild"
                :disabled="!!loading"
                class="px-4 py-2 bg-green-700 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg font-medium text-sm transition-colors"
            >
                {{ loading === 'build' ? '⏳ Building...' : '🛠 Compile Build' }}
            </button>
        </div>

        <!-- Notification -->
        <transition name="fade">
            <div
                v-if="notification"
                class="mb-4 px-4 py-2 rounded-lg text-sm"
                :class="notification.type === 'success' ? 'bg-green-900 border border-green-700 text-green-200' : 'bg-red-900 border border-red-700 text-red-200'"
            >
                {{ notification.message }}
            </div>
        </transition>

        <!-- File Lists -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <h2 class="text-sm font-semibold text-yellow-300 mb-2">Copy Files (from child)</h2>
                <div class="space-y-1">
                    <div v-for="file in copyFiles" :key="file" class="text-xs text-gray-300">
                        📄 {{ basename(file) }}
                    </div>
                    <div v-if="!copyFiles.length" class="text-xs text-gray-500 italic">None</div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                <h2 class="text-sm font-semibold text-yellow-300 mb-2">Files to remove (exceptions)</h2>
                <div class="space-y-1">
                    <div v-for="file in exceptionsFiles" :key="file" class="text-xs text-gray-300">
                        ❌ {{ basename(file) }}
                    </div>
                    <div v-if="!exceptionsFiles.length" class="text-xs text-gray-500 italic">None</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    configInfo: { type: Object, required: true },
    childProjects: { type: Object, required: true },
    copyFiles: { type: Array, default: () => [] },
    exceptionsFiles: { type: Array, default: () => [] },
});

const currentProject = ref(props.configInfo.current_project ?? '');
const buildVersion = ref(props.configInfo.build_version ?? '');
const loading = ref(null);
const notification = ref(null);

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function basename(path) {
    return path.replace(/\\/g, '/').split('/').pop();
}

function showNotification(type, message, duration = 3000) {
    notification.value = { type, message };
    setTimeout(() => { notification.value = null; }, duration);
}

async function syncToChild() {
    loading.value = 'sync';
    try {
        const res = await fetch('/copy-child');
        const data = await res.json();
        if (data.success) {
            showNotification('success', 'Sync completed successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Sync failed');
        }
    } catch {
        showNotification('error', 'Network error during sync');
    } finally {
        loading.value = null;
    }
}

async function compileBuild() {
    loading.value = 'build';
    try {
        const res = await fetch('/set-build');
        const data = await res.json();
        if (data.success) {
            showNotification('success', 'Build compiled successfully!');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('error', data.message || 'Build failed');
        }
    } catch {
        showNotification('error', 'Network error during build');
    } finally {
        loading.value = null;
    }
}

async function switchProject() {
    try {
        const res = await fetch('/switch-project', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ select: currentProject.value }),
        });
        const data = await res.json();
        if (data.success) {
            showNotification('success', 'Project switched!');
            setTimeout(() => location.reload(), 600);
        } else {
            showNotification('error', 'Failed to switch project');
        }
    } catch {
        showNotification('error', 'Network error during project switch');
    }
}

async function updateVersion() {
    try {
        const res = await fetch('/update-version', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ version: buildVersion.value }),
        });
        const data = await res.json();
        if (data.success) {
            showNotification('success', 'Version updated!');
        } else {
            showNotification('error', 'Failed to update version');
        }
    } catch {
        showNotification('error', 'Network error during version update');
    }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
