<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    files: Object,
    filters: Object
});

const search = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');
const dateFilter = ref(props.filters?.date || '');
const sizeFilter = ref(props.filters?.size || '');
const sortField = ref(props.filters?.sort_field || 'created_at');
const sortDirection = ref(props.filters?.sort_direction || 'desc');

const showFilters = ref(false);
const showPreviewModal = ref(false);
const previewFile = ref(null);

const form = useForm({});

// Types de fichiers disponibles (à adapter selon vos besoins)
const availableTypes = computed(() => {
    const types = new Set();
    if (props.files?.data) {
        props.files.data.forEach(file => {
            types.add(file.type);
        });
    }
    return Array.from(types);
});

// Filtres de taille prédéfinis
const sizesOptions = [
    { value: '', label: 'Toutes tailles' },
    { value: 'small', label: 'Petit (< 1MB)' },
    { value: 'medium', label: 'Moyen (1MB - 10MB)' },
    { value: 'large', label: 'Grand (> 10MB)' }
];

// Filtres de date prédéfinis
const dateOptions = [
    { value: '', label: 'Toutes dates' },
    { value: 'today', label: 'Aujourd\'hui' },
    { value: 'week', label: 'Cette semaine' },
    { value: 'month', label: 'Ce mois' },
    { value: 'year', label: 'Cette année' }
];

// Génère le tableau des pages à afficher dans la pagination
const paginationPages = computed(() => {
    if (!props.files || !props.files.last_page) return [];
    
    const currentPage = props.files.current_page;
    const lastPage = props.files.last_page;
    
    // Si moins de 8 pages au total, afficher toutes les pages
    if (lastPage <= 8) {
        return Array.from({ length: lastPage }, (_, i) => i + 1);
    }
    
    // Sinon, utiliser une logique pour afficher un sous-ensemble des pages
    let pages = [];
    
    // Toujours afficher la première page
    pages.push(1);
    
    // Si la page courante est proche du début
    if (currentPage <= 4) {
        pages.push(2, 3, 4, 5);
        pages.push('...');
        pages.push(lastPage - 1, lastPage);
    } 
    // Si la page courante est proche de la fin
    else if (currentPage >= lastPage - 3) {
        pages.push('...');
        pages.push(lastPage - 4, lastPage - 3, lastPage - 2, lastPage - 1, lastPage);
    } 
    // Si la page courante est au milieu
    else {
        pages.push('...');
        pages.push(currentPage - 1, currentPage, currentPage + 1);
        pages.push('...');
        pages.push(lastPage);
    }
    
    return pages;
});

// Applique les filtres et le tri
function applyFiltersAndSort() {
    router.get(route('files.index'), {
        search: search.value,
        type: typeFilter.value,
        date: dateFilter.value,
        size: sizeFilter.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, {
        preserveState: true,
        replace: true
    });
}

// Navigation vers une page spécifique
function goToPage(page) {
    if (typeof page !== 'number') return; // Ignorer les ellipses
    
    router.get(route('files.index', { page: page }), {
        search: search.value,
        type: typeFilter.value,
        date: dateFilter.value,
        size: sizeFilter.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value
    }, {
        preserveState: true
    });
}

// Surveille les changements de recherche
watch(search, (value) => {
    applyFiltersAndSort();
});

// Gère le tri en cliquant sur les en-têtes de colonnes
function toggleSort(field) {
    if (sortField.value === field) {
        // Inverse la direction si on clique sur la même colonne
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        // Définit le nouveau champ de tri et la direction par défaut (asc)
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    applyFiltersAndSort();
}

// Réinitialise tous les filtres
function resetFilters() {
    search.value = '';
    typeFilter.value = '';
    dateFilter.value = '';
    sizeFilter.value = '';
    applyFiltersAndSort();
}

// Ouvre la modal de prévisualisation pour un fichier
function openPreview(file) {
    previewFile.value = file;
    showPreviewModal.value = true;
}

// Ferme la modal de prévisualisation
function closePreview() {
    showPreviewModal.value = false;
    setTimeout(() => {
        previewFile.value = null;
    }, 300); // Petit délai pour l'animation de fermeture
}

// Vérifie si un fichier est prévisualisable
function isPreviewable(file) {
    const previewableTypes = ['pdf', 'png', 'jpg', 'jpeg', 'gif'];
    return previewableTypes.includes(file.type.toLowerCase());
}

// Obtient l'URL de prévisualisation
function getPreviewUrl(file) {
    return route('files.preview', file.id);
}

function formatSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('fr-FR', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function confirmDelete(fileId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce fichier ?')) {
        form.delete(route('files.destroy', fileId));
    }
}
</script>

<template>
    <Head title="Mes Fichiers" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mes Fichiers
                </h2>
                <Link :href="route('files.create')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                    Upload de Fichier
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Barre de recherche et options de filtrage -->
                    <div class="mb-6 space-y-4">
                        <div class="flex items-center space-x-4">
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Rechercher par nom de fichier..."
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                            >
                            <button 
                                @click="showFilters = !showFilters" 
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md flex items-center"
                            >
                                <span v-if="typeFilter || dateFilter || sizeFilter" class="mr-2 h-2 w-2 rounded-full bg-indigo-600"></span>
                                Filtres <span class="ml-1">{{ showFilters ? '▲' : '▼' }}</span>
                            </button>
                        </div>

                        <!-- Options de filtrage avancées -->
                        <div v-if="showFilters" class="p-4 bg-gray-50 rounded-md border border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Filtre par type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de fichier</label>
                                    <select 
                                        v-model="typeFilter"
                                        @change="applyFiltersAndSort"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                    >
                                        <option value="">Tous les types</option>
                                        <option v-for="type in availableTypes" :key="type" :value="type">
                                            {{ type.toUpperCase() }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filtre par date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'upload</label>
                                    <select 
                                        v-model="dateFilter"
                                        @change="applyFiltersAndSort"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                    >
                                        <option v-for="option in dateOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filtre par taille -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Taille du fichier</label>
                                    <select 
                                        v-model="sizeFilter"
                                        @change="applyFiltersAndSort"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200"
                                    >
                                        <option v-for="option in sizesOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Bouton pour réinitialiser les filtres -->
                            <div class="mt-4 flex justify-end">
                                <button 
                                    @click="resetFilters"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md"
                                >
                                    Réinitialiser les filtres
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Liste des fichiers -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="toggleSort('name')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center">
                                            Nom
                                            <span v-if="sortField === 'name'" class="ml-1">
                                                {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                            </span>
                                        </div>
                                    </th>
                                    <th @click="toggleSort('type')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center">
                                            Type
                                            <span v-if="sortField === 'type'" class="ml-1">
                                                {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                            </span>
                                        </div>
                                    </th>
                                    <th @click="toggleSort('size')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center">
                                            Taille
                                            <span v-if="sortField === 'size'" class="ml-1">
                                                {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                            </span>
                                        </div>
                                    </th>
                                    <th @click="toggleSort('created_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        <div class="flex items-center">
                                            Date d'upload
                                            <span v-if="sortField === 'created_at'" class="ml-1">
                                                {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                            </span>
                                        </div>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="!files?.data || files.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        Aucun fichier trouvé.
                                    </td>
                                </tr>
                                <tr v-for="file in files?.data" :key="file.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ file.name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full" :class="{
                                            'bg-blue-100 text-blue-800': file.type === 'pdf',
                                            'bg-green-100 text-green-800': file.type === 'docx' || file.type === 'odt',
                                            'bg-purple-100 text-purple-800': file.type === 'png' || file.type === 'jpg' || file.type === 'jpeg',
                                            'bg-yellow-100 text-yellow-800': file.type === 'xlsx' || file.type === 'csv',
                                            'bg-red-100 text-red-800': file.type === 'ppt' || file.type === 'pptx',
                                            'bg-gray-100 text-gray-800': !['pdf', 'docx', 'odt', 'png', 'jpg', 'jpeg', 'xlsx', 'csv', 'ppt', 'pptx'].includes(file.type)
                                        }">
                                            {{ file.type.toUpperCase() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatSize(file.size) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(file.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-3">
                                            <button 
                                                v-if="isPreviewable(file)" 
                                                @click="openPreview(file)" 
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Prévisualiser"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                            <a :href="route('files.download', file.id)" class="text-indigo-600 hover:text-indigo-900" title="Télécharger">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                            </a>
                                            <button @click="confirmDelete(file.id)" class="text-red-600 hover:text-red-900" title="Supprimer">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination améliorée -->
                    <div v-if="files?.links" class="mt-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center">
                            <div class="text-sm text-gray-600 mb-4 sm:mb-0">
                                Affichage de {{ files.from || 0 }} à {{ files.to || 0 }} sur {{ files.total }} fichiers
                            </div>
                            
                            <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <!-- Bouton Précédent -->
                                <button 
                                    @click="files.prev_page_url ? router.get(files.prev_page_url) : null"
                                    :class="[
                                        'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium',
                                        files.prev_page_url ? 'text-gray-500 hover:bg-gray-50' : 'text-gray-300 cursor-not-allowed'
                                    ]"
                                    :disabled="!files.prev_page_url"
                                >
                                    <span class="sr-only">Précédent</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <!-- Pages numérotées -->
                                <template v-for="(page, index) in paginationPages" :key="index">
                                    <!-- Ellipses -->
                                    <span 
                                        v-if="page === '...'" 
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                    >
                                        ...
                                    </span>
                                    
                                    <!-- Bouton de page -->
                                    <button 
                                        v-else
                                        @click="goToPage(page)"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            files.current_page === page
                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </template>
                                
                                <!-- Bouton Suivant -->
                                <button 
                                    @click="files.next_page_url ? router.get(files.next_page_url) : null"
                                    :class="[
                                        'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium',
                                        files.next_page_url ? 'text-gray-500 hover:bg-gray-50' : 'text-gray-300 cursor-not-allowed'
                                    ]"
                                    :disabled="!files.next_page_url"
                                >
                                    <span class="sr-only">Suivant</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de prévisualisation -->
        <div v-if="showPreviewModal" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
            <!-- Overlay semi-transparent -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="closePreview"></div>
            
            <!-- Contenu de la modal -->
            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
                <!-- En-tête de la modal -->
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-medium">
                        {{ previewFile?.name }}
                    </h3>
                    <button @click="closePreview" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <!-- Corps de la modal avec prévisualisation -->
                <div class="p-4 flex-1 overflow-auto">
                    <div v-if="previewFile" class="flex justify-center">
                        <!-- Prévisualisation d'image -->
                        <img 
                            v-if="['png', 'jpg', 'jpeg', 'gif'].includes(previewFile.type.toLowerCase())" 
                            :src="getPreviewUrl(previewFile)" 
                            alt="Prévisualisation" 
                            class="max-w-full max-h-[70vh] object-contain"
                        />
                        
                        <!-- Prévisualisation de PDF -->
                        <iframe 
                            v-else-if="previewFile.type.toLowerCase() === 'pdf'" 
                            :src="getPreviewUrl(previewFile)" 
                            class="w-full h-[70vh]"
                            title="Prévisualisation PDF"
                        ></iframe>
                        
                        <!-- Message pour les types non prévisualisables -->
                        <div v-else class="text-center py-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-4 text-gray-600">Ce type de fichier ne peut pas être prévisualisé.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Pied de modal avec actions -->
                <div class="border-t p-4 flex justify-end space-x-3">
                    <a 
                        v-if="previewFile" 
                        :href="route('files.download', previewFile.id)" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Télécharger
                    </a>
                    <button 
                        @click="closePreview" 
                        class="px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>