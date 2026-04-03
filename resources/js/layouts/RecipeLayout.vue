<script setup lang="ts">
import { MotionConfig } from 'motion-v'
import AppHeader from '@/components/recipe/AppHeader.vue'
import AppFooter from '@/components/recipe/AppFooter.vue'

defineProps<{
    title?: string
}>()
</script>

<template>
    <MotionConfig :reducedMotion="'user'">
        <div class="flex min-h-screen flex-col">
            <div class="no-print">
                <AppHeader />
            </div>
            <main class="flex-1 bg-white">
                <slot />
            </main>
            <div class="no-print">
                <AppFooter />
            </div>
        </div>
    </MotionConfig>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
