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
    
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 text-white pb-32 pt-20 px-8 text-center overflow-hidden">
            <!-- Decorative circle -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-0 w-64 h-64 bg-blue-400 opacity-20 rounded-full blur-2xl transform -translate-y-1/2 -translate-x-1/2"></div>
            
            <div class="relative z-10 max-w-4xl mx-auto">
                <h1 class="text-5xl font-extrabold tracking-tight mb-4 drop-shadow-md">
                    {{ tenant.data.name || 'Medical Center' }}
                </h1>
                <p class="text-xl text-blue-100 max-w-2xl mx-auto mb-8 font-light leading-relaxed">
                    Book your appointment seamlessly and avoid the waiting room hassle. Expert care is just a few clicks away.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="#booking-section" class="bg-white text-blue-700 px-8 py-3 rounded-full font-bold shadow-lg hover:shadow-xl hover:scale-105 transition-all">Book Now</a>
                </div>
            </div>
        </div>

        <!-- Main Content (Widget overlaps hero) -->
        <div id="booking-section" class="flex-grow px-4 sm:px-8 -mt-24 pb-20 relative z-20">
            <div class="max-w-3xl mx-auto">
                <BookingWidget :tenant="tenant" :doctors="doctors" />
            </div>
        </div>
        
        <footer class="bg-gray-900 text-gray-400 py-10 text-center mt-auto">
            <p class="text-sm">© {{ new Date().getFullYear() }} {{ tenant.data.name || 'Medical Center' }}. All rights reserved.</p>
        </footer>
    </div>
</template>
