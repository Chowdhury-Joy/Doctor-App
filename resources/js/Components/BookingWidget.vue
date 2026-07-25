<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const page = usePage();
const flashError = computed(() => page.props.flash?.error);

const props = defineProps({
    tenant: Object,
    doctors: { type: Array, default: () => [] },
    labSlots: { type: Array, default: () => [] },
    labTests: { type: Array, default: () => [] },
});

const activeTab = ref('doctor');

const selectedBookable = ref(null);
const availability = ref(null);
const isFetchingAvailability = ref(false);

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const form = useForm({
    bookable_type: 'session',
    bookable_id: '',
    date: '',
    name: '',
    phone: '',
    test_ids: [],
});

const toDateInputValue = (date) => date.toISOString().split('T')[0];

const nextDateForWeekday = (dayOfWeek) => {
    const date = new Date();
    const diff = (dayOfWeek - date.getDay() + 7) % 7;
    date.setDate(date.getDate() + diff);
    return toDateInputValue(date);
};

const selectedWeekdayName = computed(() =>
    selectedBookable.value ? DAY_NAMES[selectedBookable.value.day_of_week] : null
);

const fetchAvailability = async () => {
    if (!form.bookable_id || !form.date) return;
    
    isFetchingAvailability.value = true;
    try {
        const response = await axios.get(`/api/availability?bookable_type=${form.bookable_type}&bookable_id=${form.bookable_id}&date=${form.date}`);
        availability.value = response.data;
    } catch (error) {
        console.error('Failed to fetch availability', error);
        availability.value = null;
    } finally {
        isFetchingAvailability.value = false;
    }
};

const selectBookable = (bookable, type) => {
    selectedBookable.value = bookable;
    form.bookable_type = type;
    form.bookable_id = bookable.id;
    form.date = nextDateForWeekday(bookable.day_of_week);
    if (type !== 'lab_slot') {
        form.test_ids = [];
    }
    fetchAvailability();
};

const dateMin = computed(() =>
    selectedBookable.value ? nextDateForWeekday(selectedBookable.value.day_of_week) : undefined
);

const normaliseToSessionWeekday = () => {
    if (!selectedBookable.value || !form.date) return;

    const chosen = new Date(`${form.date}T00:00:00`);
    if (chosen.getDay() !== selectedBookable.value.day_of_week) {
        form.date = nextDateForWeekday(selectedBookable.value.day_of_week);
    }
};

watch(() => form.date, (newDate, oldDate) => {
    if (newDate !== oldDate && form.bookable_id) {
        fetchAvailability();
    }
});

const toggleTest = (testId) => {
    const index = form.test_ids.indexOf(testId);
    if (index === -1) {
        form.test_ids.push(testId);
    } else {
        form.test_ids.splice(index, 1);
    }
};

const selectedTestsTotal = computed(() => {
    return props.labTests
        .filter(t => form.test_ids.includes(t.id))
        .reduce((sum, t) => sum + parseFloat(t.price), 0);
});

const submitBooking = () => {
    form.post('/bookings', {
        onSuccess: () => {
            // success handles redirect by the controller
        },
    });
};
</script>

<template>
    <div class="space-y-8">
        <div v-if="labSlots.length > 0" class="flex p-1 bg-gray-100 rounded-xl mb-6">
            <button @click="activeTab = 'doctor'" :class="['flex-1 py-3 text-sm font-bold rounded-lg transition-all', activeTab === 'doctor' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700']">
                {{ __('Doctor Appointment') }}
            </button>
            <button @click="activeTab = 'lab'" :class="['flex-1 py-3 text-sm font-bold rounded-lg transition-all', activeTab === 'lab' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700']">
                {{ __('Lab Tests') }}
            </button>
        </div>

        <div v-show="activeTab === 'doctor'" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ __('Select a Doctor & Session') }}
            </h2>
            
            <div v-if="doctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="doctor in doctors" :key="doctor.id" class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow bg-white">
                    <h3 class="font-bold text-xl text-gray-900">{{ doctor.name }}</h3>
                    <p class="text-blue-600 font-medium text-sm mb-5">{{ doctor.specialty }}</p>
                    
                    <div v-if="doctor.schedule_sessions.length > 0">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">{{ __('Available Sessions') }}</h4>
                        <div class="space-y-3">
                            <button 
                                v-for="session in doctor.schedule_sessions" 
                                :key="session.id"
                                @click="selectBookable(session, 'session')"
                                :class="[
                                    'w-full text-left p-4 rounded-lg border-2 text-sm transition-all duration-200',
                                    form.bookable_id === session.id && form.bookable_type === 'session'
                                        ? 'border-blue-500 bg-blue-50/50 shadow-sm' 
                                        : 'border-transparent bg-gray-50 hover:bg-gray-100 hover:border-gray-200'
                                ]"
                            >
                                <div class="font-bold text-gray-800">{{ session.session_name }} <span class="font-normal text-gray-500">({{ session.chamber.name }})</span></div>
                                <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ session.start_time }} - {{ session.end_time }}
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-[10px] font-bold ml-auto">
                                        {{ __('Max Capacity:') }} {{ session.capacity }} {{ session.cap_type === 'day' ? __('(per day)') : __('(per session)') }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                        {{ __('No sessions scheduled at the moment.') }}
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-bold text-gray-800 mb-1">{{ __('No Doctors Available') }}</h3>
                <p class="text-gray-500 text-sm">{{ __('Please check back later or contact the clinic directly.') }}</p>
            </div>
        </div>

        <div v-show="activeTab === 'lab'" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                {{ __('Select Lab Tests & Slot') }}
            </h2>

            <div class="mb-6">
                <h3 class="font-bold text-lg text-gray-900 mb-4">{{ __('Available Tests') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <label 
                        v-for="test in labTests" 
                        :key="test.id"
                        class="flex items-start p-4 border rounded-xl cursor-pointer hover:bg-blue-50 transition-colors"
                        :class="form.test_ids.includes(test.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                    >
                        <input 
                            type="checkbox" 
                            :value="test.id" 
                            @change="toggleTest(test.id)"
                            :checked="form.test_ids.includes(test.id)"
                            class="mt-1 mr-3 rounded text-blue-600 focus:ring-blue-500" 
                        />
                        <div>
                            <div class="font-bold text-gray-800">{{ test.name }}</div>
                            <div class="text-sm text-gray-500">{{ test.department }}</div>
                            <div class="text-blue-600 font-bold mt-1">৳{{ test.price }}</div>
                        </div>
                    </label>
                </div>
            </div>

            <div v-if="labSlots.length > 0">
                <h3 class="font-bold text-lg text-gray-900 mb-4">{{ __('Collection Slots') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button 
                        v-for="slot in labSlots" 
                        :key="slot.id"
                        @click="selectBookable(slot, 'lab_slot')"
                        type="button"
                        :class="[
                            'w-full text-left p-4 rounded-lg border-2 text-sm transition-all duration-200',
                            form.bookable_id === slot.id && form.bookable_type === 'lab_slot'
                                ? 'border-blue-500 bg-blue-50/50 shadow-sm' 
                                : 'border-transparent bg-gray-50 hover:bg-gray-100 hover:border-gray-200'
                        ]"
                    >
                        <div class="font-bold text-gray-800">{{ DAY_NAMES[slot.day_of_week] }}</div>
                        <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ slot.start_time }} - {{ slot.end_time }}
                            <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-[10px] font-bold ml-auto">
                                {{ __('Max:') }} {{ slot.slot_cap }}
                            </span>
                        </div>
                    </button>
                </div>
            </div>
            <div v-else class="text-sm text-gray-500 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                {{ __('No collection slots available.') }}
            </div>
        </div>

        <div v-if="selectedBookable" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100 transform transition-all animate-fade-in-up">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                {{ __('Patient Details') }}
            </h2>
            
            <form @submit.prevent="submitBooking" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Appointment Date') }}</label>
                        <input
                            type="date"
                            v-model="form.date"
                            :min="dateMin"
                            step="7"
                            required
                            @change="normaliseToSessionWeekday"
                            class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors"
                        />
                        <p v-if="selectedWeekdayName" class="text-xs text-gray-500 mt-1">
                            {{ activeTab === 'doctor' ? __('This session runs every') : __('This slot runs every') }} {{ selectedWeekdayName }}.
                        </p>

                        <!-- Availability Indicator -->
                        <div v-if="isFetchingAvailability" class="text-xs text-blue-500 mt-2 flex items-center gap-1">
                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ __('Checking availability...') }}
                        </div>
                        <div v-else-if="availability" class="text-xs mt-2 font-medium" :class="availability.available > 0 ? 'text-green-600' : 'text-red-500'">
                            <span v-if="availability.available > 0">{{ availability.available }} {{ __('slots available') }} ({{ availability.booked }}/{{ availability.capacity }} {{ __('booked') }})</span>
                            <span v-else>{{ __('Session is fully booked for this date!') }}</span>
                        </div>
                        
                        <div v-if="form.errors.date" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.date }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Patient Name') }}</label>
                        <input type="text" v-model="form.name" :placeholder="__('John Doe')" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.name }}</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('Phone Number') }}</label>
                    <input type="tel" v-model="form.phone" placeholder="+8801700000000" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                    <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.phone }}</div>
                </div>

                <div v-if="activeTab === 'lab' && selectedTestsTotal > 0" class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <div class="flex justify-between items-center text-gray-900 font-bold">
                        <span>{{ __('Total Test Price:') }}</span>
                        <span class="text-xl text-blue-600">৳{{ selectedTestsTotal }}</span>
                    </div>
                    <div v-if="form.errors.test_ids" class="text-red-500 text-xs mt-2 font-medium">{{ form.errors.test_ids }}</div>
                </div>
                
                <div v-if="form.errors.error || flashError" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ form.errors.error || flashError }}</span>
                </div>

                <div class="pt-4">
                    <button type="submit" :disabled="form.processing || (availability && availability.available === 0) || (activeTab === 'lab' && form.test_ids.length === 0)" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 transition-all transform hover:-translate-y-0.5">
                        <span v-if="availability && availability.available === 0">{{ __('Fully Booked') }}</span>
                        <span v-else-if="activeTab === 'lab' && form.test_ids.length === 0">{{ __('Select at least one test') }}</span>
                        <span v-else>{{ __('Confirm Appointment') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>
