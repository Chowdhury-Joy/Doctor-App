<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    serial: Object
});

const nowServing = ref(0);
let pollInterval = null;

const currentUrl = ref('');
const copyStatus = ref('idle');

onMounted(() => {
    currentUrl.value = window.location.href;
    fetchStatus();
    pollInterval = setInterval(fetchStatus, 5000); // Poll every 5 seconds
});

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(currentUrl.value);
        copyStatus.value = 'copied';
        setTimeout(() => { copyStatus.value = 'idle'; }, 2000);
    } catch (e) {
        console.error("Failed to copy", e);
    }
};

const fetchStatus = async () => {
    try {
        const response = await fetch(`/queue/status/${props.serial.schedule_session_id}?date=${props.serial.booking_date}`);
        const data = await response.json();
        nowServing.value = data.now_serving;
    } catch (e) {
        console.error("Failed to fetch queue status", e);
    }
};


onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head :title="__('Booking Ticket')" />
    
    <div class="min-h-screen bg-gray-50 p-8 flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-blue-600 p-6 text-center text-white">
                <h1 class="text-2xl font-bold mb-1">{{ __('Booking Confirmed!') }}</h1>
                <p class="text-blue-100 text-sm">{{ __('Save this link or take a screenshot') }}</p>
            </div>
            
            <div class="p-6">
                <div class="text-center mb-8">
                    <div class="text-sm text-gray-500 uppercase tracking-widest mb-1">{{ __('Your Serial Number') }}</div>
                    <div class="text-6xl font-black text-gray-900">{{ serial.serial_number }}</div>
                </div>

                <div class="bg-gray-50 rounded p-4 mb-6 text-center border">
                    <div class="text-xs text-gray-500 uppercase tracking-widest mb-1">{{ __('Currently Serving') }}</div>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ nowServing === 0 ? __('Waiting to start') : '#' + nowServing }}
                    </div>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">{{ __('Patient Name') }}</span>
                        <span class="font-medium text-gray-900">{{ serial.patient_name }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">{{ __('Doctor') }}</span>
                        <span class="font-medium text-gray-900">{{ serial.doctor.name }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">{{ __('Chamber') }}</span>
                        <span class="font-medium text-gray-900">{{ serial.chamber.name }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">{{ __('Date') }}</span>
                        <span class="font-medium text-gray-900">{{ serial.booking_date }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">{{ __('Session') }}</span>
                        <span class="font-medium text-gray-900">{{ serial.schedule_session.session_name }} ({{ serial.schedule_session.start_time }} - {{ serial.schedule_session.end_time }})</span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="text-gray-500">{{ __('Payment Status') }}</span>
                        <span :class="[
                            'font-medium uppercase text-xs px-2 py-1 rounded',
                            serial.payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'
                        ]">{{ serial.payment_status }}</span>
                    </div>
                </div>
            </div>
            
            <div class="px-6 pb-6">
                <div class="mt-4 p-3 bg-gray-50 border rounded flex items-center justify-between gap-3">
                    <input type="text" readonly :value="currentUrl" class="text-xs text-gray-500 bg-transparent border-none w-full p-0 focus:ring-0" />
                    <button @click="copyLink" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded font-medium transition-colors shrink-0">
                        {{ copyStatus === 'copied' ? __('Copied!') : __('Copy Link') }}
                    </button>
                </div>
            </div>
            
            <div class="bg-gray-100 p-4 text-center text-xs text-gray-500">
                {{ __('Ticket ID:') }} {{ serial.id }}
            </div>
        </div>
    </div>
</template>
