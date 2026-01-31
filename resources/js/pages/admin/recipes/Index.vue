<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'

defineProps<{
    recipes: Array<{
        id: number
        title: string
        description: string
        image: string | null
        ingredients: string[]
    }>
}>()

const deletingId = ref<number | null>(null)

function confirmDelete(id: number): void {
    if (confirm('Möchten Sie dieses Rezept wirklich löschen?')) {
        deletingId.value = id
        router.delete(`/admin/recipes/${id}`, {
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
                        Administratorbereich
                    </h1>
                    <div class="flex gap-3 flex-wrap">
                        <Link
                            href="/settings/profile"
                            class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg border-2 border-[var(--color-forest)]/20 hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Einstellungen
                        </Link>
                        <Link
                            href="/admin/about/edit"
                            class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg border-2 border-[var(--color-forest)]/20 hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Über Mich
                        </Link>
                        <Link
                            href="/admin/recipes/create"
                            class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Neues Rezept
                        </Link>
                    </div>
                </div>

                <div v-if="recipes.length === 0" class="text-center py-12 text-[var(--color-warm-gray)]">
                    Noch keine Rezepte vorhanden.
                </div>

                <div v-else class="bg-white rounded-xl shadow-md border border-[var(--color-forest)]/5 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[var(--color-forest)]/5">
                                <tr>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Titel
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Beschreibung
                                    </th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-[var(--color-forest)]">
                                        Zutaten
                                    </th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[var(--color-forest)]">
                                        Aktionen
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--color-forest)]/10">
                                <tr
                                    v-for="recipe in recipes"
                                    :key="recipe.id"
                                    class="hover:bg-[var(--color-forest)]/5 transition-colors"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            <div v-if="recipe.image" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden border border-[var(--color-forest)]/10">
                                                <img
                                                    :src="recipe.image"
                                                    :alt="recipe.title"
                                                    class="w-full h-full object-cover"
                                                />
                                            </div>
                                            <span class="font-heading font-semibold text-[var(--color-forest)]">
                                                {{ recipe.title }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-[var(--color-warm-gray)] line-clamp-2 max-w-md">
                                            {{ recipe.description }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-md">
                                            <ul class="text-sm text-[var(--color-warm-gray)] space-y-1">
                                                <li
                                                    v-for="(ingredient, index) in recipe.ingredients.slice(0, 3)"
                                                    :key="index"
                                                    class="flex items-start gap-2"
                                                >
                                                    <span class="text-[var(--color-terracotta)] mt-1">•</span>
                                                    <span>{{ ingredient }}</span>
                                                </li>
                                                <li v-if="recipe.ingredients.length > 3" class="text-[var(--color-warm-gray)]/70 italic">
                                                    + {{ recipe.ingredients.length - 3 }} weitere
                                                </li>
                                                <li v-if="recipe.ingredients.length === 0" class="text-[var(--color-warm-gray)]/70 italic">
                                                    Keine Zutaten
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <Link
                                                :href="`/admin/recipes/${recipe.id}/edit`"
                                                class="px-4 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-forest)]/5 rounded-lg hover:bg-[var(--color-forest)]/10 transition-colors"
                                            >
                                                Bearbeiten
                                            </Link>
                                            <button
                                                type="button"
                                                @click="confirmDelete(recipe.id)"
                                                :disabled="deletingId === recipe.id"
                                                class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors disabled:opacity-50"
                                            >
                                                {{ deletingId === recipe.id ? 'Löschen...' : 'Löschen' }}
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
