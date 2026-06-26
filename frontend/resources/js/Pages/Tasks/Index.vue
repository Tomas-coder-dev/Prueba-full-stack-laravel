<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';

const tasks = ref([]);
const isLoading = ref(true);
const isSaving = ref(false);
const hasError = ref(false);

const filters = ref({ search: '', status: '', priority: '' });

const isModalOpen = ref(false);
const isEditing = ref(false);
const formErrors = ref({});

const defaultForm = () => ({
    id: null,
    title: '',
    description: '',
    due_date: '',
    status: 'pending',
    priority: 'medium',
});

const form = ref(defaultForm());

const statusMap = {
    pending:     { label: 'Pendiente',   class: 'badge-warning' },
    in_progress: { label: 'En progreso', class: 'badge-accent'  },
    done:        { label: 'Completada',  class: 'badge-success' },
};

const priorityMap = {
    low:    { label: 'Baja',  class: 'badge-neutral' },
    medium: { label: 'Media', class: 'badge-warning' },
    high:   { label: 'Alta',  class: 'badge-danger'  },
};

const dueDateClass = (date) => {
    if (!date) return '';
    const diff = (new Date(date) - new Date()) / 86400000;
    if (diff < 0) return 'text-red-500';
    if (diff <= 3) return 'text-yellow-600';
    return 'text-gray-500';
};

const taskCount = computed(() => tasks.value.length);

const fetchTasks = async () => {
    isLoading.value = true;
    hasError.value = false;
    try {
        const { data } = await axios.get('/tasks', { params: filters.value });
        tasks.value = data;
    } catch {
        hasError.value = true;
    } finally {
        isLoading.value = false;
    }
};

watch(filters, fetchTasks, { deep: true });

const openModal = (task = null) => {
    formErrors.value = {};
    form.value = task ? { ...task } : defaultForm();
    isEditing.value = !!task;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    formErrors.value = {};
};

const saveTask = async () => {
    formErrors.value = {};
    isSaving.value = true;
    try {
        if (isEditing.value) {
            await axios.put(`/tasks/${form.value.id}`, form.value);
        } else {
            await axios.post('/tasks', form.value);
        }
        closeModal();
        fetchTasks();
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors;
        }
    } finally {
        isSaving.value = false;
    }
};

const deleteTask = async (id) => {
    if (!confirm('¿Eliminar esta tarea? Esta acción no se puede deshacer.')) return;
    try {
        await axios.delete(`/tasks/${id}`);
        fetchTasks();
    } catch {
        // silently ignore
    }
};

onMounted(fetchTasks);
</script>

<template>
    <Head title="Mis tareas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 leading-tight">Mis tareas</h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ isLoading ? 'Cargando…' : `${taskCount} tarea${taskCount !== 1 ? 's' : ''}` }}
                    </p>
                </div>
                <button
                    @click="openModal()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva tarea
                </button>
            </div>
        </template>

        <div class="py-10">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

                <div v-if="hasError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                    No se pudieron cargar las tareas. Intenta de nuevo.
                </div>

                <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 11A6 6 0 111 11a6 6 0 0116 0z" />
                        </svg>
                        <input
                            type="text"
                            v-model="filters.search"
                            placeholder="Buscar por título..."
                            class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        >
                    </div>
                    <select
                        v-model="filters.status"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none transition"
                    >
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="done">Completada</option>
                    </select>
                    <select
                        v-model="filters.priority"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none transition"
                    >
                        <option value="">Todas las prioridades</option>
                        <option value="low">Baja</option>
                        <option value="medium">Media</option>
                        <option value="high">Alta</option>
                    </select>
                </div>

                <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                    <div v-if="isLoading" class="p-8 space-y-3">
                        <div v-for="i in 4" :key="i" class="animate-pulse flex gap-4">
                            <div class="h-4 bg-gray-100 rounded flex-1"></div>
                            <div class="h-4 bg-gray-100 rounded w-24"></div>
                            <div class="h-4 bg-gray-100 rounded w-20"></div>
                            <div class="h-4 bg-gray-100 rounded w-16"></div>
                        </div>
                    </div>

                    <div v-else-if="tasks.length === 0" class="py-16 text-center">
                        <svg class="mx-auto w-10 h-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-sm font-medium text-gray-500">Sin tareas</p>
                        <p class="text-xs text-gray-400 mt-1">Ajusta los filtros o crea una nueva tarea.</p>
                    </div>

                    <table v-else class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Título</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Vencimiento</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Estado</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Prioridad</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr
                                v-for="task in tasks"
                                :key="task.id"
                                class="hover:bg-gray-50 transition-colors"
                                :class="{ 'opacity-60': task.status === 'done' }"
                            >
                                <td class="px-5 py-4">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                        :class="{ 'line-through text-gray-400': task.status === 'done' }"
                                    >
                                        {{ task.title }}
                                    </p>
                                    <p v-if="task.description" class="text-xs text-gray-400 mt-0.5 truncate max-w-xs">
                                        {{ task.description }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-sm" :class="dueDateClass(task.due_date)">
                                    {{ task.due_date || '—' }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-yellow-50 text-yellow-700': task.status === 'pending',
                                            'bg-blue-50 text-blue-700':   task.status === 'in_progress',
                                            'bg-green-50 text-green-700': task.status === 'done',
                                        }"
                                    >
                                        {{ statusMap[task.status]?.label ?? task.status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="{
                                            'bg-gray-100 text-gray-500':    task.priority === 'low',
                                            'bg-yellow-50 text-yellow-700': task.priority === 'medium',
                                            'bg-red-50 text-red-600':       task.priority === 'high',
                                        }"
                                    >
                                        {{ priorityMap[task.priority]?.label ?? task.priority }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right">
                                    <button
                                        @click="openModal(task)"
                                        :disabled="task.status === 'done'"
                                        class="text-blue-600 hover:text-blue-800 text-sm mr-4 disabled:opacity-30 disabled:cursor-not-allowed transition"
                                        :title="task.status === 'done' ? 'Las tareas completadas no se pueden editar' : 'Editar tarea'"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        @click="deleteTask(task.id)"
                                        class="text-red-500 hover:text-red-700 text-sm transition"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-semibold text-gray-900">
                        {{ isEditing ? 'Editar tarea' : 'Nueva tarea' }}
                    </h2>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <InputLabel for="title" value="Título" />
                        <TextInput
                            id="title"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.title"
                            placeholder="Nombre de la tarea"
                            :class="{ 'border-red-400': formErrors.title }"
                        />
                        <p v-if="formErrors.title" class="mt-1 text-xs text-red-500">{{ formErrors.title[0] }}</p>
                    </div>

                    <div>
                        <InputLabel for="description" value="Descripción" />
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            placeholder="Detalle opcional..."
                            class="mt-1 block w-full text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg resize-none"
                        ></textarea>
                        <p v-if="formErrors.description" class="mt-1 text-xs text-red-500">{{ formErrors.description[0] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="due_date" value="Vencimiento" />
                            <TextInput
                                id="due_date"
                                type="date"
                                class="mt-1 block w-full"
                                v-model="form.due_date"
                                :class="{ 'border-red-400': formErrors.due_date }"
                            />
                            <p v-if="formErrors.due_date" class="mt-1 text-xs text-red-500">{{ formErrors.due_date[0] }}</p>
                        </div>

                        <div>
                            <InputLabel for="priority" value="Prioridad" />
                            <select
                                id="priority"
                                v-model="form.priority"
                                class="mt-1 block w-full text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                            >
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                            </select>
                            <p v-if="formErrors.priority" class="mt-1 text-xs text-red-500">{{ formErrors.priority[0] }}</p>
                        </div>
                    </div>

                    <div v-if="isEditing">
                        <InputLabel for="status" value="Estado" />
                        <select
                            id="status"
                            v-model="form.status"
                            class="mt-1 block w-full text-sm border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg"
                        >
                            <option value="pending">Pendiente</option>
                            <option value="in_progress">En progreso</option>
                            <option value="done">Completada</option>
                        </select>
                        <p v-if="formErrors.status" class="mt-1 text-xs text-red-500">{{ formErrors.status[0] }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <PrimaryButton @click="saveTask" :disabled="isSaving" class="min-w-[100px]">
                        <span v-if="isSaving" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Guardando…
                        </span>
                        <span v-else>Guardar</span>
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>