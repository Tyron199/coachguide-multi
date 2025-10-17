<template>
    <Teleport to="body">
        <div v-if="page.props.auth.impersonating" ref="bannerRef" :style="{
            left: `${position.x}px`,
            top: `${position.y}px`,
            transform: 'translate(-50%, 0)'
        }" class="fixed z-50 bg-amber-500 text-white px-4 py-3 rounded-lg shadow-2xl flex items-center gap-4 border border-amber-600 transition-shadow hover:shadow-amber-500/50"
            :class="{ 'cursor-move': !isStoppingImpersonation, 'cursor-grabbing': isDragging }">
            <div class="flex items-center gap-2 select-none" @mousedown="startDrag" @touchstart="startDrag">
                <div class="flex items-center justify-center w-8 h-8 bg-white/20 rounded-full">
                    <GripVertical class="h-4 w-4 opacity-70" />
                </div>
                <p class="font-medium text-sm">
                    Impersonating {{ page.props.auth.impersonating.current_user_name }}
                </p>
            </div>
            <Button variant="secondary" size="sm" @click="stopImpersonating" @mousedown.stop @touchstart.stop
                :disabled="isStoppingImpersonation" class="h-8">
                <LogOut class="mr-1.5 h-3.5 w-3.5" />
                Stop
            </Button>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { GripVertical, LogOut } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import type { AppPageProps } from '@/types';
import impersonate from '@/routes/tenant/impersonate';

const page = usePage<AppPageProps>();
const isStoppingImpersonation = ref(false);
const isDragging = ref(false);
const bannerRef = ref<HTMLElement | null>(null);

// Load position from localStorage or use default (bottom center)
const getInitialPosition = () => {
    const saved = localStorage.getItem('impersonation-banner-position');
    if (saved) {
        return JSON.parse(saved);
    }
    // Default: bottom center
    return {
        x: window.innerWidth / 2,
        y: window.innerHeight - 80
    };
};

const position = ref(getInitialPosition());

const dragOffset = ref({ x: 0, y: 0 });

const startDrag = (e: MouseEvent | TouchEvent) => {
    if (isStoppingImpersonation.value) return;

    isDragging.value = true;

    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;

    dragOffset.value = {
        x: clientX - position.value.x,
        y: clientY - position.value.y
    };

    e.preventDefault();
};

const onDrag = (e: MouseEvent | TouchEvent) => {
    if (!isDragging.value || !bannerRef.value) return;

    const clientX = 'touches' in e ? e.touches[0].clientX : e.clientX;
    const clientY = 'touches' in e ? e.touches[0].clientY : e.clientY;

    // Get banner dimensions
    const bannerRect = bannerRef.value.getBoundingClientRect();
    const bannerWidth = bannerRect.width;
    const bannerHeight = bannerRect.height;

    // Calculate new position
    let newX = clientX - dragOffset.value.x;
    let newY = clientY - dragOffset.value.y;

    // Apply boundaries (accounting for the -50% transform on X axis)
    const halfWidth = bannerWidth / 2;
    const minX = halfWidth;
    const maxX = window.innerWidth - halfWidth;
    const minY = 0;
    const maxY = window.innerHeight - bannerHeight;

    // Clamp position within viewport
    newX = Math.max(minX, Math.min(maxX, newX));
    newY = Math.max(minY, Math.min(maxY, newY));

    position.value = {
        x: newX,
        y: newY
    };

    e.preventDefault();
};

const stopDrag = () => {
    if (isDragging.value) {
        isDragging.value = false;
        // Save position to localStorage
        localStorage.setItem('impersonation-banner-position', JSON.stringify(position.value));
    }
};

const stopImpersonating = () => {
    isStoppingImpersonation.value = true;
    router.post(
        impersonate.stop().url,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isStoppingImpersonation.value = false;
            }
        }
    );
};

// Ensure banner stays within viewport bounds
const constrainPosition = () => {
    if (!bannerRef.value) return;

    const bannerRect = bannerRef.value.getBoundingClientRect();
    const bannerWidth = bannerRect.width;
    const bannerHeight = bannerRect.height;

    const halfWidth = bannerWidth / 2;
    const minX = halfWidth;
    const maxX = window.innerWidth - halfWidth;
    const minY = 0;
    const maxY = window.innerHeight - bannerHeight;

    position.value = {
        x: Math.max(minX, Math.min(maxX, position.value.x)),
        y: Math.max(minY, Math.min(maxY, position.value.y))
    };
};

onMounted(() => {
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('touchmove', onDrag);
    document.addEventListener('touchend', stopDrag);
    window.addEventListener('resize', constrainPosition);

    // Ensure initial position is within bounds
    setTimeout(constrainPosition, 0);
});

onUnmounted(() => {
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('touchmove', onDrag);
    document.removeEventListener('touchend', stopDrag);
    window.removeEventListener('resize', constrainPosition);
});
</script>
