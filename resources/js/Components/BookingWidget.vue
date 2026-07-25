<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';

const page = usePage();
const flashError = computed(() => page.props.flash?.error);

const props = defineProps({
    tenant: Object,
    doctors: Array
});

const selectedSession = ref(null);
const availability = ref(null);
const isFetchingAvailability = ref(false);

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const form = useForm({
    session_id: '',
    date: '',
    name: '',
    phone: '',
});

const toDateInputValue = (date) => date.toISOString().split('T')[0];

// The next date (today or later) that falls on the given day_of_week (0=Sun..6=Sat).
const nextDateForWeekday = (dayOfWeek) => {
    const date = new Date();
    const diff = (dayOfWeek - date.getDay() + 7) % 7;
    date.setDate(date.getDate() + diff);
    return toDateInputValue(date);
};

// Sessions only run on one weekday, so the date field is locked to the
// selected session's weekday instead of accepting any date and only
// rejecting the mismatch after the whole form is submitted.
const selectedWeekdayName = computed(() =>
    selectedSession.value ? DAY_NAMES[selectedSession.value.day_of_week] : null
);

const fetchAvailability = async () => {
    if (!form.session_id || !form.date) return;
    
    isFetchingAvailability.value = true;
    try {
        const response = await axios.get(`/api/sessions/${form.session_id}/availability?date=${form.date}`);
        availability.value = response.data;
    } catch (error) {
        console.error('Failed to fetch availability', error);
        availability.value = null;
    } finally {
        isFetchingAvailability.value = false;
    }
};

const selectSession = (session) => {
    selectedSession.value = session;
    form.session_id = session.id;
    form.date = nextDateForWeekday(session.day_of_week);
    fetchAvailability();
};

// The date input is limited to the session's weekday via step-by-7-days
// arithmetic from its first valid occurrence, so the picker itself can't
// land on a mismatched day.
const dateMin = computed(() =>
    selectedSession.value ? nextDateForWeekday(selectedSession.value.day_of_week) : undefined
);

const normaliseToSessionWeekday = () => {
    if (!selectedSession.value || !form.date) return;

    const chosen = new Date(`${form.date}T00:00:00`);
    if (chosen.getDay() !== selectedSession.value.day_of_week) {
        form.date = nextDateForWeekday(selectedSession.value.day_of_week);
    }
};

watch(() => form.date, (newDate, oldDate) => {
    if (newDate !== oldDate && form.session_id) {
        fetchAvailability();
    }
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
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Select a Doctor & Session
            </h2>
            
            <div v-if="doctors.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="doctor in doctors" :key="doctor.id" class="border border-gray-200 rounded-xl p-5 hover:shadow-md transition-shadow bg-white">
                    <h3 class="font-bold text-xl text-gray-900">{{ doctor.name }}</h3>
                    <p class="text-blue-600 font-medium text-sm mb-5">{{ doctor.specialty }}</p>
                    
                    <div v-if="doctor.schedule_sessions.length > 0">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Available Sessions</h4>
                        <div class="space-y-3">
                            <button 
                                v-for="session in doctor.schedule_sessions" 
                                :key="session.id"
                                @click="selectSession(session)"
                                :class="[
                                    'w-full text-left p-4 rounded-lg border-2 text-sm transition-all duration-200',
                                    form.session_id === session.id 
                                        ? 'border-blue-500 bg-blue-50/50 shadow-sm' 
                                        : 'border-transparent bg-gray-50 hover:bg-gray-100 hover:border-gray-200'
                                ]"
                            >
                                <div class="font-bold text-gray-800">{{ session.session_name }} <span class="font-normal text-gray-500">({{ session.chamber.name }})</span></div>
                                <div class="text-xs text-gray-600 mt-1 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ session.start_time }} - {{ session.end_time }}
                                    <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-[10px] font-bold ml-auto">
                                        Max Capacity: {{ session.capacity }} {{ session.cap_type === 'day' ? '(per day)' : '(per session)' }}
                                    </span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-sm text-gray-500 bg-gray-50 p-4 rounded-lg border border-dashed border-gray-300">
                        No sessions scheduled at the moment.
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-bold text-gray-800 mb-1">No Doctors Available</h3>
                <p class="text-gray-500 text-sm">Please check back later or contact the clinic directly.</p>
            </div>
        </div>

        <div v-if="selectedSession" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-gray-100 transform transition-all animate-fade-in-up">
            <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-3">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Patient Details
            </h2>
            
            <form @submit.prevent="submitBooking" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Appointment Date</label>
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
                            This session runs every {{ selectedWeekdayName }}.
                        </p>

                        <!-- Availability Indicator -->
                        <div v-if="isFetchingAvailability" class="text-xs text-blue-500 mt-2 flex items-center gap-1">
                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Checking availability...
                        </div>
                        <div v-else-if="availability" class="text-xs mt-2 font-medium" :class="availability.available > 0 ? 'text-green-600' : 'text-red-500'">
                            <span v-if="availability.available > 0">{{ availability.available }} slots available ({{ availability.booked }}/{{ availability.capacity }} booked)</span>
                            <span v-else>Session is fully booked for this date!</span>
                        </div>
                        
                        <div v-if="form.errors.date" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.date }}</div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Patient Name</label>
                        <input type="text" v-model="form.name" placeholder="John Doe" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.name }}</div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" v-model="form.phone" placeholder="+8801700000000" required class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-inner focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors" />
                    <div v-if="form.errors.phone" class="text-red-500 text-xs mt-1 font-medium">{{ form.errors.phone }}</div>
                </div>
                
                <div v-if="form.errors.error || flashError" class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>{{ form.errors.error || flashError }}</span>
                </div>

                <div class="pt-4">
                    <button type="submit" :disabled="form.processing || (availability && availability.available === 0)" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-indigo-700 focus:ring-4 focus:ring-blue-500/50 disabled:opacity-50 transition-all transform hover:-translate-y-0.5">
                        <span v-if="availability && availability.available === 0">Fully Booked</span>
                        <span v-else>Confirm Appointment</span>
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
