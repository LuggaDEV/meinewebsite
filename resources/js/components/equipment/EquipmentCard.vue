<script setup lang="ts">
import { Motion } from 'motion-v'
import type { Equipment } from '@/types/equipment'
import Badge from '@/components/ui/badge/Badge.vue'

defineProps<{
    equipment: Equipment
}>()
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20 }"
        :in-view="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.5, ease: 'easeOut' }"
        :viewport="{ once: true, margin: '-50px' }"
    >
        <article class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden border border-[var(--color-forest)]/5 w-full">
            <div class="aspect-[4/3] overflow-hidden bg-[var(--color-forest)]/5">
                <img
                    v-if="equipment.image"
                    :src="equipment.image"
                    :alt="equipment.name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-[var(--color-forest)]/5">
                    <svg class="w-16 h-16 text-[var(--color-forest)]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="p-5 md:p-6">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h3 class="font-heading text-lg md:text-xl font-semibold text-[var(--color-forest)] group-hover:text-[var(--color-terracotta)] transition-colors flex-1">
                        {{ equipment.name }}
                    </h3>
                    <Badge variant="secondary" class="shrink-0">
                        {{ equipment.category }}
                    </Badge>
                </div>
                <a
                    :href="equipment.link"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-[var(--color-forest)] text-white rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                >
                    Ansehen
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </article>
    </Motion>
</template>
