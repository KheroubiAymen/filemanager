<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Liste des types de fichiers acceptés avec descriptions
const acceptedFileTypes = [
    { type: 'pdf', label: 'PDF', icon: 'document-text' },
    { type: 'docx', label: 'Word', icon: 'document' },
    { type: 'odt', label: 'OpenDocument', icon: 'document' },
    { type: 'png', label: 'PNG', icon: 'photograph' },
    { type: 'jpg', label: 'JPG/JPEG', icon: 'photograph' }
];

// Formats pour l'attribut "accept" de l'input file
const acceptedFormats = '.pdf,.docx,.png,.jpg,.jpeg,.odt';

// Taille maximale des fichiers (10 Mo en octets)
const MAX_FILE_SIZE = 10 * 1024 * 1024;

// État du composant
const dragOver = ref(false);
const showErrorModal = ref(false);
const errorModalType = ref('type'); // 'type', 'size', ou 'both'
const errorFiles = ref([]);
const oversizedFiles = ref([]);
const selectedFiles = ref([]);

// Formulaire Inertia
const form = useForm({
    files: []
});

// Vérifie si le fichier est d'un type accepté
function isAcceptedFileType(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    return ['pdf', 'docx', 'png', 'jpg', 'jpeg', 'odt'].includes(extension);
}

// Vérifie si le fichier est de taille acceptable
function isAcceptableSize(file) {
    return file.size <= MAX_FILE_SIZE;
}

// Gère la sélection de fichiers
function handleFileSelection(event) {
    const files = event.target.files || event.dataTransfer.files;
    
    // Réinitialiser les listes
    errorFiles.value = [];
    oversizedFiles.value = [];
    selectedFiles.value = [];
    
    // Vérifier chaque fichier
    Array.from(files).forEach(file => {
        // Vérification du type de fichier
        const isValidType = isAcceptedFileType(file);
        
        // Vérification de la taille
        const isValidSize = isAcceptableSize(file);
        
        if (isValidType && isValidSize) {
            // Fichier valide
            selectedFiles.value.push(file);
        } else {
            // Fichier invalide
            if (!isValidType) {
                errorFiles.value.push(file);
            }
            
            if (!isValidSize) {
                oversizedFiles.value.push(file);
            }
        }
    });
    
    // Mettre à jour le formulaire avec uniquement les fichiers valides
    form.files = selectedFiles.value;
    
    // Afficher le modal d'erreur si nécessaire
    if (errorFiles.value.length > 0 || oversizedFiles.value.length > 0) {
        if (errorFiles.value.length > 0 && oversizedFiles.value.length > 0) {
            errorModalType.value = 'both';
        } else if (errorFiles.value.length > 0) {
            errorModalType.value = 'type';
        } else {
            errorModalType.value = 'size';
        }
        showErrorModal.value = true;
    }
    
    // Réinitialiser l'input file pour permettre de sélectionner à nouveau le même fichier
    if (event.target.type === 'file') {
        event.target.value = '';
    }
    
    dragOver.value = false;
}

// Formate la taille du fichier
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Soumettre le formulaire
function submit() {
    // Vérification supplémentaire avant soumission
    if (selectedFiles.value.length === 0) {
        return;
    }
    
    form.post(route('files.store'), {
        onSuccess: () => {
            // Réinitialiser le formulaire et la liste de fichiers
            form.reset();
            selectedFiles.value = [];
        }
    });
}

// Pour le drag & drop
function handleDragOver(event) {
    event.preventDefault();
    dragOver.value = true;
}

function handleDragLeave() {
    dragOver.value = false;
}

function handleDrop(event) {
    event.preventDefault();
    handleFileSelection(event);
}

// Supprime un fichier de la liste de sélection
function removeFile(index) {
    selectedFiles.value.splice(index, 1);
    form.files = selectedFiles.value;
}
</script>

<template>
    <Head title="Upload de Fichiers" />
    
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Upload de Fichiers
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <!-- Information sur les types de fichiers acceptés -->
                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-medium text-blue-800 mb-2">Types de fichiers acceptés</h3>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <div v-for="fileType in acceptedFileTypes" :key="fileType.type" class="flex items-center">
                                <span class="text-blue-600 mr-1">
                                    <svg v-if="fileType.icon === 'document'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <svg v-else-if="fileType.icon === 'document-text'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <svg v-else-if="fileType.icon === 'photograph'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <span class="text-blue-800">{{ fileType.label }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-blue-600 mt-2">
                            Taille maximale par fichier: 10 MB | Maximum 5 fichiers par upload
                        </p>
                    </div>

                    <!-- Zone de drop -->
                    <div 
                        @dragover="handleDragOver"
                        @dragleave="handleDragLeave"
                        @drop="handleDrop"
                        :class="{ 'border-indigo-500 bg-indigo-50': dragOver, 'border-gray-300': !dragOver }"
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition-colors duration-150"
                    >
                        <div class="space-y-1 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                    <span>Choisir des fichiers</span>
                                    <input 
                                        type="file" 
                                        multiple 
                                        class="sr-only" 
                                        :accept="acceptedFormats"
                                        @change="handleFileSelection"
                                    >
                                </label>
                                <p class="pl-1">ou glisser-déposer</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, Word, OpenDocument, PNG, JPG
                            </p>
                        </div>
                    </div>

                    <!-- Liste des fichiers sélectionnés -->
                    <div v-if="selectedFiles.length > 0" class="mt-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Fichiers sélectionnés</h3>
                        <ul class="divide-y divide-gray-200 border border-gray-200 rounded-md">
                            <li v-for="(file, index) in selectedFiles" :key="index" class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                <div class="w-0 flex-1 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="ml-2 flex-1 w-0 truncate">
                                        {{ file.name }}
                                        <span class="text-gray-500 text-xs ml-1">({{ formatFileSize(file.size) }})</span>
                                    </span>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <button @click="removeFile(index)" type="button" class="font-medium text-red-600 hover:text-red-500">
                                        Supprimer
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <Link :href="route('files.index')" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Annuler
                        </Link>
                        <button 
                            @click="submit"
                            :disabled="form.processing || selectedFiles.length === 0"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || selectedFiles.length === 0 }"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Envoi en cours...' : 'Envoyer les fichiers' }}
                        </button>
                    </div>
                    
                    <!-- Affichage des erreurs du formulaire -->
                    <div v-if="form.errors.files" class="mt-4 text-sm text-red-600">
                        {{ form.errors.files }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal d'erreur pour les fichiers non valides -->
        <div v-if="showErrorModal" class="fixed inset-0 overflow-y-auto z-50 flex items-center justify-center">
            <!-- Overlay semi-transparent -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            
            <!-- Contenu de la modal -->
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <!-- En-tête de la modal -->
                <div class="bg-red-100 rounded-t-lg p-4 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="text-lg font-medium text-red-800">
                        Fichiers non valides
                    </h3>
                </div>
                
                <!-- Corps de la modal -->
                <div class="p-6">
                    <!-- Erreurs de type de fichier -->
                    <div v-if="errorFiles.length > 0">
                        <p class="text-gray-700 mb-3">
                            Les fichiers suivants ne peuvent pas être uploadés car ils ne sont pas dans un format accepté :
                        </p>
                        
                        <ul class="list-disc pl-5 text-sm text-gray-600 mb-4">
                            <li v-for="(file, index) in errorFiles" :key="`type-${index}`" class="mb-1">
                                {{ file.name }} ({{ file.type || 'Type inconnu' }})
                            </li>
                        </ul>
                    </div>

                    <!-- Erreurs de taille de fichier -->
                    <div v-if="oversizedFiles.length > 0">
                        <p class="text-gray-700 mb-3" :class="{ 'mt-4': errorFiles.length > 0 }">
                            Les fichiers suivants dépassent la taille maximale autorisée (10 MB) :
                        </p>
                        
                        <ul class="list-disc pl-5 text-sm text-gray-600 mb-4">
                            <li v-for="(file, index) in oversizedFiles" :key="`size-${index}`" class="mb-1">
                                {{ file.name }} ({{ formatFileSize(file.size) }})
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Rappel des formats acceptés -->
                    <div v-if="errorFiles.length > 0">
                        <p class="text-gray-700 mb-2 font-medium">
                            Formats acceptés :
                        </p>
                        
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div v-for="fileType in acceptedFileTypes" :key="fileType.type" class="text-sm text-gray-600">
                                • {{ fileType.label }} (.{{ fileType.type }})
                            </div>
                        </div>
                    </div>

                    <!-- Rappel de la taille maximale -->
                    <div v-if="oversizedFiles.length > 0">
                        <p class="text-gray-700 mb-2 font-medium">
                            Limite de taille :
                        </p>
                        
                        <p class="text-sm text-gray-600 mb-4">
                            Chaque fichier doit être inférieur à 10 MB.
                        </p>
                    </div>
                </div>
                
                <!-- Pied de modal avec bouton de fermeture -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                    <button 
                        @click="showErrorModal = false" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>