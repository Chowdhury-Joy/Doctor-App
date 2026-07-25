<script setup>
import { Head } from '@inertiajs/vue3';
import BookingWidget from '@/Components/BookingWidget.vue';
import SectionRenderer from '@/Components/SectionRenderer.vue';
import Footer from '@/Components/Sections/Footer.vue';

defineProps({
    tenant: Object,
    doctors: Array
});
</script>

<template>
    <Head :title="tenant.name || 'Medical Center'" />
    
    <div class="min-h-screen bg-teal-50 font-sans flex flex-col">
        <header class="bg-teal-700 text-white shadow-md">
            <div class="max-w-6xl mx-auto py-5 px-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-wide">
                        {{ tenant.name || 'Clinic' }}
                    </h1>
                </div>
                <div class="hidden sm:flex text-teal-100 text-sm font-medium gap-6">
                    <span>{{ __('Emergency') }}: {{ tenant.contact_phone || '911' }}</span>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto py-10 px-4 sm:px-6 flex-grow w-full">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-6 rounded border-t-4 border-teal-600 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">{{ tenant.tagline || 'Welcome to our Clinic' }}</h3>
                        <p class="text-gray-600 text-sm">{{ tenant.about_text || 'We provide world-class medical services with state-of-the-art facilities.' }}</p>
                    </div>
                    
                    <div v-if="tenant.features && tenant.features.length" class="bg-teal-700 text-white p-6 rounded shadow-sm">
                        <h3 class="text-lg font-bold mb-4">{{ __('Why Choose Us?') }}</h3>
                        <ul class="space-y-3 text-sm text-teal-50">
                            <li v-for="(feature, idx) in tenant.features" :key="idx" class="flex gap-2 items-start">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ feature.title }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column (Booking Widget usually) -->
                <div class="lg:col-span-8">
                    <SectionRenderer :tenant="tenant" :doctors="doctors">
                        <!-- Hide redundant sections if displayed in sidebar -->
                        <template #hero><div></div></template>
                        <template #about><div></div></template>
                        <template #features><div></div></template>
                    </SectionRenderer>
                </div>
            </div>
        </main>
        
        <Footer :tenant="tenant" />
    </div>
</template>
