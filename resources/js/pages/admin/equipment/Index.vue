<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import type { Equipment } from '@/types/equipment'

defineProps<{
    equipment: Equipment[]
}>()

const deletingId = ref<number | null>(null)

function confirmDelete(id: number): void {
    if (confirm('Möchten Sie dieses Equipment wirklich löschen?')) {
        deletingId.value = id
        router.delete(`/admin/equipment/${id}`, {
            preserveScroll: true,
            onFinish: () => {
                deletingId.value = null
            },
        })
    }
}
</script>

<template>
    <RecipeLayout>
        <div class="py-12 md:py-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)]">
                        Equipment-Verwaltung
                    </h1>
                    <div class="flex gap-3 flex-wrap">
                        <Link
                            href="/admin/recipes"
                            class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg border-2 border-[var(--color-forest)]/20 hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Rezepte
                        </Link>
                        <Link
                            href="/admin/equipment/create"
                            class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Neues Equipment
                        </Link>
                    </div>
                </div>

                <div v-if="equipment.length === 0" class="text-center py-12 text-[var(--color-warm-gray)]">
                    Noch kein Equipment vorhanden.
                </div>

                <div v-else class="bg-white rounded-xl shadow-md border border-[var(--color-forest)]/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[var(--color-forest)]/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Name
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Kategorie
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Link
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[var(--color-forest)]">
                                        Aktionen
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--color-forest)]/10">
                                <tr
                                    v-for="item in equipment"
                                    :key="item.id"
                                    class="hover:bg-[var(--color-forest)]/5 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div v-if="item.image" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-[var(--color-forest)]/10">
                                                <img
                                                    :src="item.image"
                                                    :alt="item.name"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                            <span class="font-heading font-semibold text-[var(--color-forest)]">
                                                {{ item.name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[var(--color-forest)]/10 text-[var(--color-forest)]">
                                            {{ item.category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a
                                            :href="item.link"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm text-[var(--color-terracotta)] hover:underline truncate block max-w-xs"
                                        >
                                            {{ item.link }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link
                                                :href="`/admin/equipment/${item.id}/edit`"
                                                class="px-4 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-forest)]/5 rounded-lg hover:bg-[var(--color-forest)]/10 transition-colors"
                                            >
                                                Bearbeiten
                                            </Link>
                                            <button
                                                type="button"
                                                @click="confirmDelete(item.id)"
                                                :disabled="deletingId === item.id"
                                                class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors disabled:opacity-50"
                                            >
                                                {{ deletingId === item.id ? 'Löschen...' : 'Löschen' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </RecipeLayout>
</template>
