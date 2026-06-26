<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/Stores/useAuthStore';
import axios from 'axios';

const authStore = useAuthStore();
const isLoading = ref(true);
const hasError = ref(false);

const metrics = ref({
    total_tasks: 0,
    pending_tasks: 0,
    in_progress_tasks: 0,
    completed_tasks: 0,
});

const cards = [
    {
        key: 'total_tasks',
        label: 'Total',
        sub: 'Todas las tareas',
        icon: 'layout-list',
        color: '',
    },
    {
        key: 'pending_tasks',
        label: 'Pendientes',
        sub: 'Esperando inicio',
        icon: 'clock-pause',
        color: 'warning',
    },
    {
        key: 'in_progress_tasks',
        label: 'En progreso',
        sub: 'Actualmente activas',
        icon: 'loader',
        color: 'blue',
    },
    {
        key: 'completed_tasks',
        label: 'Completadas',
        sub: 'Finalizadas con éxito',
        icon: 'circle-check',
        color: 'success',
    },
];

const colorMap = {
    '': {
        border: 'border-l-gray-300',
        icon: 'bg-gray-100 text-gray-500',
        value: 'text-gray-900',
    },
    warning: {
        border: 'border-l-yellow-400',
        icon: 'bg-yellow-50 text-yellow-600',
        value: 'text-yellow-600',
    },
    blue: {
        border: 'border-l-blue-500',
        icon: 'bg-blue-50 text-blue-600',
        value: 'text-blue-600',
    },
    success: {
        border: 'border-l-green-500',
        icon: 'bg-green-50 text-green-600',
        value: 'text-green-600',
    },
};

async function fetchMetrics() {
    isLoading.value = true;
    hasError.value = false;
    try {
        const { data } = await axios.get('/dashboard');
        metrics.value = data;
    } catch {
        hasError.value = true;
    } finally {
        isLoading.value = false;
    }
}

onMounted(() => {
    const token = localStorage.getItem('jwt_token');

    if (!token) {
        window.location.href = '/login';
        return;
    }

    if (!authStore.token) {
        authStore.token = token;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        const storedUser = localStorage.getItem('user');
        if (storedUser) authStore.user = JSON.parse(storedUser);
    }

    fetchMetrics();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 leading-tight">Panel de control</h2>
                    <p class="text-sm text-gray-500 mt-0.5">Resumen de tus tareas activas</p>
                </div>
                <button
                    @click="fetchMetrics"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
                >
                    <svg class="w-4 h-4" :class="{ 'animate-spin': isLoading }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Actualizar
                </button>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <div v-if="hasError" class="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-600 mb-6">
                    No se pudieron cargar las métricas. Intenta de nuevo.
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="card in cards"
                        :key="card.key"
                        class="bg-white rounded-xl border-l-4 border border-gray-100 overflow-hidden transition-shadow hover:shadow-sm"
                        :class="colorMap[card.color].border"
                    >
                        <div class="p-5">
                            <div class="flex items-start justify-between mb-4">
                                <div class="p-2 rounded-lg" :class="colorMap[card.color].icon">
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <template v-if="card.icon === 'layout-list'">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </template>
                                        <template v-else-if="card.icon === 'clock-pause'">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </template>
                                        <template v-else-if="card.icon === 'loader'">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </template>
                                        <template v-else-if="card.icon === 'circle-check'">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </template>
                                    </svg>
                                </div>
                            </div>

                            <div v-if="isLoading" class="animate-pulse space-y-2">
                                <div class="h-7 w-12 bg-gray-100 rounded"></div>
                                <div class="h-3 w-20 bg-gray-100 rounded"></div>
                                <div class="h-3 w-24 bg-gray-100 rounded mt-3"></div>
                            </div>

                            <div v-else>
                                <p class="text-2xl font-semibold" :class="colorMap[card.color].value">
                                    {{ metrics[card.key] }}
                                </p>
                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mt-0.5">{{ card.label }}</p>
                                <p class="text-xs text-gray-400 mt-3">{{ card.sub }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>