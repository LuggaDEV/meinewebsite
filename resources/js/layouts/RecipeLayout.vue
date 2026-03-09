<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppHeader from '@/components/recipe/AppHeader.vue'
import AppFooter from '@/components/recipe/AppFooter.vue'

defineProps<{
    title?: string
}>()
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <div class="no-print">
            <AppHeader />
        </div>
        <main class="flex-1">
            <slot />
        </main>
        <div class="no-print">
            <AppFooter />
        </div>
    </div>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
