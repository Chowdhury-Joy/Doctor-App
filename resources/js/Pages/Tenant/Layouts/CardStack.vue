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
    
    <div class="min-h-screen bg-gray-100 flex flex-col font-sans py-12 px-4 sm:px-6">
        <div class="w-full max-w-4xl mx-auto space-y-6">
            <SectionRenderer :tenant="tenant" :doctors="doctors">
                <!-- Override Hero -->
                <template #hero="{ section, tenant }">
                    <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center gap-8 justify-between relative overflow-hidden">
                        <div class="absolute right-0 top-0 w-64 h-64 bg-emerald-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
                        
                        <div class="relative z-10 text-center sm:text-left">
                            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 tracking-tight mb-4">
                                {{ tenant.name || 'Medical Center' }}
                            </h1>
                            <p class="text-gray-600 text-lg">
                                {{ tenant.tagline || 'Modern healthcare, simplified. Book your consultation in minutes.' }}
                            </p>
                        </div>
                        
                        <div class="hidden sm:flex shrink-0 w-24 h-24 bg-emerald-100 rounded-full items-center justify-center text-emerald-600 relative z-10 shadow-inner">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                    </div>
                </template>

                <!-- Let other sections render naturally inside the max-w-4xl wrapper -->
                <!-- The doctors section now handles its own display and links to /book -->
                <!-- For now, they will just render as they are -->
            </SectionRenderer>
            
            <footer class="text-center text-gray-500 text-sm py-6">
                &copy; {{ new Date().getFullYear() }} {{ tenant.name || 'Medical Center' }}. {{ __('All rights reserved.') }}
            </footer>
        </div>
    </div>
</template>
