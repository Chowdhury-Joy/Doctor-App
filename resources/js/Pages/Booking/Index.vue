<script setup>
import { Head } from '@inertiajs/vue3';
import BookingWidget from '@/Components/BookingWidget.vue';

defineProps({
    tenant: Object,
    doctors: Array,
    labSlots: Array,
    labTests: Array
});
</script>

<template>
    <Head :title="tenant.name ? `${tenant.name} - Book Appointment` : 'Book Appointment'" />
    
    <div class="min-h-screen bg-gray-50 font-sans py-12 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8 text-center">
                <a href="/" class="text-blue-600 hover:underline mb-4 inline-block">&larr; {{ __('Back to home') }}</a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Book an Appointment') }}</h1>
                <p class="text-gray-600 mt-2">{{ tenant.name }}</p>
            </div>
            
            <BookingWidget :tenant="tenant" :doctors="doctors" :labSlots="labSlots" :labTests="labTests" class="bg-white rounded-xl shadow-md border border-gray-100 p-6 sm:p-10" />
        </div>
    </div>
</template>
