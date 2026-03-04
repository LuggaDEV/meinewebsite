<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue'
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'
import { Star } from 'lucide-vue-next'
import { edit as adminRecipeEdit } from '@/routes/admin/recipes'
import adminRecipeReviews from '@/routes/admin/recipes/reviews'

interface ReviewItem {
    id: number
    recipe_id: number
    recipe_title: string | null
    rating: number
    body: string | null
    author_name: string | null
    user: { id: number; name: string } | null
    reply: string | null
    replied_at: string | null
    created_at: string
}

defineProps<{
    reviews: ReviewItem[]
}>()

const reviewReplyDraft = ref<Record<number, string>>({})
const deleteModalOpen = ref(false)
const reviewToDelete = ref<ReviewItem | null>(null)

function authorDisplay(r: ReviewItem): string {
    return r.author_name ?? r.user?.name ?? 'Gast'
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('de-DE', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

function getReplyDraft(r: ReviewItem): string {
    return reviewReplyDraft.value[r.id] ?? r.reply ?? ''
}

function setReplyDraft(r: ReviewItem, value: string): void {
    reviewReplyDraft.value[r.id] = value
}

function submitReply(review: ReviewItem): void {
    const reply = (reviewReplyDraft.value[review.id] ?? review.reply ?? '').trim()
    const url = adminRecipeReviews.reply.url(
        { recipe: review.recipe_id, review: review.id },
        { query: { return_to_reviews: 1 } },
    )
    router.put(url, { reply: reply || null }, { preserveScroll: true })
}

function openDeleteModal(review: ReviewItem): void {
    reviewToDelete.value = review
    deleteModalOpen.value = true
}

function cancelDelete(): void {
    deleteModalOpen.value = false
    reviewToDelete.value = null
}

function confirmDelete(): void {
    if (!reviewToDelete.value) return
    const { recipe_id, id } = reviewToDelete.value
    const url = adminRecipeReviews.destroy.url(
        { recipe: recipe_id, review: id },
        { query: { return_to_reviews: 1 } },
    )
    router.delete(url, { preserveScroll: true })
    cancelDelete()
}
</script>

<template>
    <AdminLayout title="Bewertungen" subtitle="Rezeptbewertungen verwalten und beantworten">
        <div v-if="reviews.length === 0">
            <AdminEmptyState
                title="Noch keine Bewertungen"
                description="Bewertungen erscheinen hier, sobald Besucher Rezepte bewertet haben."
                :icon="Star"
            />
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="r in reviews"
                :key="r.id"
                class="rounded-xl border border-[var(--color-forest)]/10 bg-white p-4 shadow-sm md:p-6"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <Link
                            :href="adminRecipeEdit.url(r.recipe_id)"
                            class="font-heading font-semibold text-[var(--color-forest)] hover:text-[var(--color-terracotta)] hover:underline"
                        >
                            {{ r.recipe_title ?? 'Rezept' }}
                        </Link>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-[var(--color-warm-gray)]">
                            <span class="font-medium text-[var(--color-forest)]">{{ authorDisplay(r) }}</span>
                            <span>{{ formatDate(r.created_at) }}</span>
                            <span
                                class="flex gap-0.5"
                                :aria-label="`${r.rating} von 5 Sternen`"
                            >
                                <template v-for="i in 5" :key="i">
                                    <span
                                        :class="
                                            i <= r.rating
                                                ? 'text-[var(--color-terracotta)]'
                                                : 'text-[var(--color-warm-gray)]/40'
                                        "
                                        class="text-sm"
                                    >
                                        ★
                                    </span>
                                </template>
                            </span>
                        </div>
                    </div>
                    <Link
                        :href="adminRecipeEdit.url(r.recipe_id)"
                        class="shrink-0 rounded-lg border border-[var(--color-forest)]/20 px-3 py-1.5 text-sm font-medium text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5"
                    >
                        Zum Rezept
                    </Link>
                </div>

                <p v-if="r.body" class="mt-3 text-sm text-[var(--color-warm-gray)]">
                    {{ r.body }}
                </p>

                <div
                    v-if="r.reply"
                    class="mt-3 border-l-2 border-[var(--color-terracotta)]/30 pl-3"
                >
                    <p class="mb-1 text-xs font-medium text-[var(--color-forest)]">
                        Deine Antwort
                    </p>
                    <p class="text-sm text-[var(--color-warm-gray)]">{{ r.reply }}</p>
                    <p
                        v-if="r.replied_at"
                        class="mt-1 text-xs text-[var(--color-warm-gray)]/80"
                    >
                        {{ formatDate(r.replied_at) }}
                    </p>
                </div>

                <div class="mt-4 flex flex-wrap gap-3">
                    <div class="min-w-0 flex-1">
                        <textarea
                            :value="getReplyDraft(r)"
                            rows="2"
                            maxlength="2000"
                            class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-sm focus:ring-2 focus:ring-[var(--color-terracotta)]"
                            :placeholder="r.reply ? 'Antwort bearbeiten…' : 'Antwort schreiben…'"
                            @input="setReplyDraft(r, ($event.target as HTMLTextAreaElement).value)"
                        />
                    </div>
                    <div class="flex flex-shrink-0 gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-[var(--color-terracotta)] px-3 py-2 text-sm font-medium text-white transition-colors hover:opacity-90"
                            @click="submitReply(r)"
                        >
                            {{ r.reply ? 'Aktualisieren' : 'Antworten' }}
                        </button>
                        <button
                            type="button"
                            class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                            @click="openDeleteModal(r)"
                        >
                            Löschen
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <ConfirmDeleteModal
            :open="deleteModalOpen"
            title="Bewertung löschen"
            message="Möchten Sie diese Bewertung wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden."
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AdminLayout>
</template>
