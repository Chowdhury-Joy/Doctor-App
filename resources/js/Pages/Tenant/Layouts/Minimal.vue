<script setup>
import { Head } from '@inertiajs/vue3';
import BookingWidget from '@/Components/BookingWidget.vue';

defineProps({
    tenant: Object,
    doctors: Array
});
</script>

<template>
    <Head :title="tenant.data.name || 'Medical Center'" />
    
    <div class="min-h-screen bg-white text-gray-900 font-sans">
        <header class="border-b border-gray-100 py-6 px-8">
            <div class="max-w-5xl mx-auto flex justify-between items-center">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ tenant.data.name || 'Medical Center' }}
                </h1>
                <a href="#" class="text-sm font-medium text-gray-500 hover:text-black transition-colors">Contact Support</a>
            </div>
        </header>

        <main class="max-w-5xl mx-auto py-12 px-4 sm:px-8">
            <div class="mb-12">
                <h2 class="text-4xl font-light mb-4">Book your appointment.</h2>
                <p class="text-gray-500 max-w-xl text-lg">Select a specialist and choose a time that works for you. Fast, secure, and hassle-free.</p>
            </div>
            
            <BookingWidget :tenant="tenant" :doctors="doctors" class="!shadow-none !border !border-gray-200 !bg-gray-50/30" />
        </main>
    </div>
</template>
