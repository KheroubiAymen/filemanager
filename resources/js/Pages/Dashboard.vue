<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tableau de bord</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <h3 class="text-2xl font-bold mb-4">Bienvenue dans votre gestionnaire de fichiers</h3>
                        <p class="text-lg mb-6">Commencez à uploader et gérer vos fichiers en toute simplicité</p>
                        
                        <Link 
                            :href="route('files.create')"
                            class="inline-flex items-center px-5 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-white text-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                        >
                            Commencer à uploader vos fichiers
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>