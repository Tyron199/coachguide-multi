<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Calendar, CheckCircle, Clock, Users, Settings } from 'lucide-vue-next';
import CalendarIntegrationController from '@/actions/App/Http/Controllers/Tenant/Settings/CalendarIntegrationController';
interface Props {
    hasMicrosoftCalendar: boolean;
    hasGoogleCalendar?: boolean;
    show?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    hasGoogleCalendar: false
});

const emit = defineEmits<{
    close: [];
    notNow: [];
}>();

const isOpen = ref(false);

// Key for localStorage to track if user has seen this modal
const MODAL_SEEN_KEY = 'calendar_connect_modal_seen';

onMounted(() => {
    // Only show if:
    // 1. User doesn't have any calendar connected
    // 2. User hasn't seen this modal before
    // 3. Component prop allows showing
    const hasAnyCalendar = props.hasMicrosoftCalendar || props.hasGoogleCalendar;

    if (!hasAnyCalendar && !hasSeenModal() && props.show) {
        // Small delay for better UX
        setTimeout(() => {
            isOpen.value = true;
        }, 1000);
    }
});

function hasSeenModal(): boolean {
    return localStorage.getItem(MODAL_SEEN_KEY) === 'true';
}

function markModalAsSeen(): void {
    localStorage.setItem(MODAL_SEEN_KEY, 'true');
}

function handleClose(): void {
    isOpen.value = false;
    markModalAsSeen();
    emit('close');
}

function handleGoToIntegrations(): void {
    isOpen.value = false;
    markModalAsSeen();
    // Navigate to integrations page
    window.location.href = CalendarIntegrationController.show().url;
}

function handleNotNow(): void {
    isOpen.value = false;
    markModalAsSeen();

    emit('notNow');
}
</script>

<template>
    <Dialog v-model:open="isOpen" @update:open="handleClose">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <Calendar class="h-5 w-5 text-primary" />
                    Calendar Integration Available
                </DialogTitle>
                <DialogDescription>
                    Connect your Microsoft or Google Calendar to automatically sync coaching sessions with your
                    calendar.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <!-- Benefits -->
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <CheckCircle class="h-5 w-5 text-green-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-sm">Two-Way Sync</p>
                            <p class="text-xs text-muted-foreground">Sessions sync both ways between CoachGuide and your
                                calendar</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Clock class="h-5 w-5 text-blue-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-sm">Smart Reminders</p>
                            <p class="text-xs text-muted-foreground">Never miss a session with calendar notifications
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <Users class="h-5 w-5 text-purple-600 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium text-sm">Multiple Providers</p>
                            <p class="text-xs text-muted-foreground">Choose between Microsoft Calendar or Google
                                Calendar</p>
                        </div>
                    </div>
                </div>

                <!-- Privacy note -->
                <div class="bg-muted p-3 rounded-md">
                    <p class="text-xs text-muted-foreground">
                        🔒 Your calendar data is encrypted and only used for session synchronization.
                        You can manage or disconnect at any time from your integration settings.
                    </p>
                </div>
            </div>

            <DialogFooter class="flex gap-2">
                <Button @click="handleGoToIntegrations" class="flex-1">
                    <Settings class="h-4 w-4 mr-2" />
                    Setup Calendar Integration
                </Button>
                <Button variant="outline" @click="handleNotNow" class="flex-1">
                    Not Now
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>