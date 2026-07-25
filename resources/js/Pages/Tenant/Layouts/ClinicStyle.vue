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
    
    <div class="min-h-screen bg-teal-50 font-sans">
        <header class="bg-teal-700 text-white shadow-md">
            <div class="max-w-6xl mx-auto py-5 px-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded flex items-center justify-center">
                        <svg class="w-6 h-6 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-wide">
                        {{ tenant.data.name || 'Clinic' }}
                    </h1>
                </div>
                <div class="hidden sm:flex text-teal-100 text-sm font-medium gap-6">
                    <span>Emergency: 911</span>
                    <span>Opening Hours: 24/7</span>
                </div>
            </div>
        </header>

        <main class="max-w-6xl mx-auto py-10 px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Info Sidebar -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-6 rounded border-t-4 border-teal-600 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Welcome to our Clinic</h3>
                        <p class="text-gray-600 text-sm">We provide world-class medical services with state-of-the-art facilities. Book your slot using our digital portal.</p>
                    </div>
                    
                    <div class="bg-teal-700 text-white p-6 rounded shadow-sm">
                        <h3 class="text-lg font-bold mb-4">Why Choose Us?</h3>
                        <ul class="space-y-3 text-sm text-teal-50">
                            <li class="flex gap-2 items-start">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Qualified Professionals
                            </li>
                            <li class="flex gap-2 items-start">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Modern Infrastructure
                            </li>
                            <li class="flex gap-2 items-start">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Patient-first approach
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Booking Widget -->
                <div class="lg:col-span-8">
                    <BookingWidget :tenant="tenant" :doctors="doctors" class="!shadow-md !rounded !border-t-4 !border-teal-600 !bg-white" />
                </div>
            </div>
        </main>
    </div>
</template>
