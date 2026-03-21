<template>
    <div class="min-h-screen bg-gray-900 text-gray-100">
        <nav class="bg-gray-800 border-b border-gray-700 px-6 py-3 flex gap-6">
            <router-link
                to="/"
                class="text-sm font-medium transition-colors"
                :class="$route.path === '/' ? 'text-yellow-400' : 'text-gray-400 hover:text-gray-200'"
            >
                ⚔️ Sync
            </router-link>
            <router-link
                to="/units"
                class="text-sm font-medium transition-colors"
                :class="$route.path === '/units' ? 'text-yellow-400' : 'text-gray-400 hover:text-gray-200'"
            >
                📋 Units
            </router-link>
        </nav>

        <router-view />
    </div>
</template>
