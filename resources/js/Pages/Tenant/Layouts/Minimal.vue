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
    <Head :title="tenant.name || 'Booking'" />
    
    <div class="min-h-screen bg-white font-sans flex flex-col">
        <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 py-12 flex-grow">
            <div class="text-center mb-10">
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    {{ tenant.name || 'Medical Appointment' }}
                </h1>
                <p class="text-gray-500 mt-2 text-sm">{{ __('Select a service and time.') }}</p>
            </div>
            
            <SectionRenderer :tenant="tenant" :doctors="doctors">
                <!-- Keep minimal by hiding large sections -->
                <template #hero><div></div></template>
                <template #about><div></div></template>
                <template #features><div></div></template>
                <template #contact><div></div></template>
                <template #footer><div></div></template>
                
                <!-- Default sections will be rendered -->
            </SectionRenderer>
        </div>
        
        <Footer :tenant="tenant" />
    </div>
</template>
