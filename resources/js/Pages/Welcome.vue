<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <main class="flex items-center h-screen justify-center">
        <div class="max-w-full p-6 text-center">
            <p class="pt-4 text-2xl font-bold text-gray-600 dark:text-accent">
                Welcome to
            </p>
            <p class="pt-4 text-7xl font-bold text-primary dark:text-accent">
                Sri Lanka Laksiri Portal
            </p>
            <div v-if="canLogin" class="py-5 space-x-5">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="btn bg-success font-medium text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="btn bg-success font-medium text-white hover:bg-success-focus focus:bg-success-focus active:bg-success-focus/90"
                    >
                        Login
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="btn bg-warning font-medium text-white hover:bg-warning-focus focus:bg-warning-focus active:bg-warning-focus/90"
                    >
                        Register
                    </Link>
                </template>
            </div>
        </div>
    </main>
</template>
