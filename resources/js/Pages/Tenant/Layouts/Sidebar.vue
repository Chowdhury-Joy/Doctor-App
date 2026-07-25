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
    
    <div class="min-h-screen bg-gray-50 flex flex-col md:flex-row font-sans">
        <!-- Sidebar -->
        <div class="md:w-1/3 lg:w-1/4 bg-blue-900 text-white p-10 flex flex-col justify-between shadow-2xl z-10 relative">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-32 -left-32 w-64 h-64 bg-blue-600 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-blob"></div>
                <div class="absolute top-1/2 -right-32 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>
            </div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center mb-8 shadow-inner">
                    <span class="text-3xl font-black text-blue-900">{{ (tenant.data.name || 'M')[0] }}</span>
                </div>
                
                <h1 class="text-4xl font-black mb-4 leading-tight">
                    {{ tenant.data.name || 'Medical Center' }}
                </h1>
                
                <p class="text-blue-200 text-lg mb-8 opacity-90">
                    Your health is our priority. Schedule your visit with our specialists today.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-bold text-sm">Opening Hours</p>
                            <p class="text-blue-200 text-sm">Mon-Sun: 8AM - 10PM</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="relative z-10 mt-12 pt-8 border-t border-blue-800/50">
                <p class="text-xs text-blue-400">© {{ new Date().getFullYear() }} Powered by DoctorApp</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="md:w-2/3 lg:w-3/4 p-6 md:p-12 lg:p-16 flex items-center overflow-y-auto">
            <div class="w-full max-w-2xl mx-auto">
                <BookingWidget :tenant="tenant" :doctors="doctors" />
            </div>
        </div>
    </div>
</template>
