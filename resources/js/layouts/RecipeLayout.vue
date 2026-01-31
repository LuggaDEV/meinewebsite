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
        <AppHeader />
        <main class="flex-1">
            <slot />
        </main>
        <AppFooter />
    </div>
</template>
