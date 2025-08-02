<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="text-center mb-6 p-4 bg-gradient-to-r from-indigo-500 to-blue-500 text-white rounded-t-lg -mx-6 -mt-4">
                <h1 class="text-2xl font-bold">Simple File Manager</h1>
                <p class="mt-2">Connectez-vous à votre compte</p>
            </div>
            
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ml-2 text-sm text-gray-600">Se souvenir de moi</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <Link
                        v-if="$page.props.canResetPassword"
                        :href="route('password.request')"
                        class="underline text-sm text-gray-600 hover:text-gray-900"
                    >
                        Mot de passe oublié?
                    </Link>

                    <Link
                        :href="route('register')"
                        class="ml-4 underline text-sm text-gray-600 hover:text-gray-900"
                    >
                        Créer un compte
                    </Link>

                    <PrimaryButton class="ml-4 transition ease-in-out duration-150 transform hover:-translate-y-1 hover:shadow-lg" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Connexion
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

<style>
.bg-gradient-to-r {
    background: linear-gradient(135deg, #6366f1, #3b82f6);
}
</style>