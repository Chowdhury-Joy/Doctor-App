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
    
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <SectionRenderer :tenant="tenant" :doctors="doctors">
            <!-- Override Hero for custom chrome -->
            <template #hero="{ section, tenant }">
                <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 text-white pb-32 pt-20 px-8 text-center overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
                    <div class="absolute top-1/2 left-0 w-64 h-64 bg-blue-400 opacity-20 rounded-full blur-2xl transform -translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 max-w-4xl mx-auto">
                        <h1 class="text-5xl font-extrabold tracking-tight mb-4 drop-shadow-md">
                            {{ tenant.name || 'Medical Center' }}
                        </h1>
                        <p class="text-xl text-blue-100 max-w-2xl mx-auto mb-8 font-light leading-relaxed">
                            {{ tenant.tagline || 'Book your appointment seamlessly and avoid the waiting room hassle.' }}
                        </p>
                        <div v-if="section.show_button !== false" class="flex justify-center gap-4">
                            <a href="#booking-section" class="bg-white text-blue-700 px-8 py-3 rounded-full font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">{{ __('Book Now') }}</a>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Let doctors section render naturally below hero -->
        </SectionRenderer>
        
        <Footer :tenant="tenant" />
    </div>
</template>
