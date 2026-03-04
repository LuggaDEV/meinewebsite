<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminEmptyState from '@/components/admin/AdminEmptyState.vue'
import ConfirmDeleteModal from '@/components/admin/ConfirmDeleteModal.vue'
import { ChefHat } from 'lucide-vue-next'
import type { Equipment } from '@/types/equipment'
import { create, destroy, edit } from '@/routes/admin/equipment'

defineProps<{
    equipment: Equipment[]
}>()

const deletingId = ref<number | null>(null)
const deleteTargetId = ref<number | null>(null)
const modalOpen = ref(false)

function openDeleteModal(id: number): void {
    deleteTargetId.value = id
    modalOpen.value = true
}

function confirmDelete(): void {
    if (deleteTargetId.value === null) return
    const id = deleteTargetId.value
    deletingId.value = id
    modalOpen.value = false
    deleteTargetId.value = null
    router.delete(destroy.url(id), {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null
        },
    })
}

function cancelDelete(): void {
    modalOpen.value = false
    deleteTargetId.value = null
}
</script>

<template>
    <AdminLayout title="Equipment" subtitle="Küchenequipment und Empfehlungen verwalten">
        <template #actions>
            <Link
                :href="create.url()"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--color-forest)] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-forest)] focus:ring-offset-2"
            >
                <ChefHat class="h-4 w-4" />
                Neues Equipment
            </Link>
        </template>

        <div v-if="equipment.length === 0">
            <AdminEmptyState
                title="Noch kein Equipment"
                description="Fügen Sie Küchengeräte und Empfehlungen hinzu, die Sie in Rezepten verlinken möchten."
                :icon="ChefHat"
            >
                <Link
                    :href="create.url()"
                    class="inline-flex items-center gap-2 rounded-lg bg-[var(--color-forest)] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[var(--color-terracotta)]"
                >
                    <ChefHat class="h-4 w-4" />
                    Equipment anlegen
                </Link>
            </AdminEmptyState>
        </div>

        <div v-else class="space-y-4">
            <!-- Mobile/Tablet: Cards -->
            <div class="space-y-4 md:hidden">
                <div
                    v-for="item in equipment"
                    :key="item.id"
                    class="overflow-hidden rounded-xl border border-[var(--color-forest)]/10 bg-white shadow-sm"
                >
                    <div class="flex gap-4 p-4">
                        <div
                            v-if="item.image"
                            class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-[var(--color-forest)]/10"
                        >
                            <img
                                :src="item.image"
                                :alt="item.name"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="font-heading font-semibold text-[var(--color-forest)]">
                                    {{ item.name }}
                                </h3>
                                <span class="rounded-full bg-[var(--color-forest)]/10 px-2 py-0.5 text-xs font-medium text-[var(--color-forest)]">
                                    {{ item.category }}
                                </span>
                            </div>
                            <a
                                v-if="item.link"
                                :href="item.link"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-1 block truncate text-sm text-[var(--color-terracotta)] hover:underline"
                            >
                                {{ item.link }}
                            </a>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <Link
                                    :href="edit.url(item.id)"
                                    class="rounded-lg bg-[var(--color-forest)]/10 px-3 py-1.5 text-sm font-medium text-[var(--color-forest)] hover:bg-[var(--color-forest)]/15"
                                >
                                    Bearbeiten
                                </Link>
                                <button
                                    type="button"
                                    :disabled="deletingId === item.id"
                                    class="rounded-lg bg-red-50 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-100 disabled:opacity-50"
                                    @click="openDeleteModal(item.id)"
                                >
                                    {{ deletingId === item.id ? 'Löschen…' : 'Löschen' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop: Table -->
            <div class="hidden overflow-hidden rounded-xl border border-[var(--color-forest)]/10 bg-white shadow-sm md:block">
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
                                <th class="px-6 py-4 text-right text-sm font-semibold text-[var(--color-forest)]">
                                    Aktionen
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--color-forest)]/10">
                            <tr
                                v-for="item in equipment"
                                :key="item.id"
                                class="transition-colors hover:bg-[var(--color-forest)]/5"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div
                                            v-if="item.image"
                                            class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-[var(--color-forest)]/10"
                                        >
                                            <img
                                                :src="item.image"
                                                :alt="item.name"
                                                class="h-full w-full object-cover"
                                            />
                                        </div>
                                        <span class="font-heading font-semibold text-[var(--color-forest)]">
                                            {{ item.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full bg-[var(--color-forest)]/10 px-3 py-1 text-sm font-medium text-[var(--color-forest)]">
                                        {{ item.category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a
                                        v-if="item.link"
                                        :href="item.link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="max-w-xs truncate text-sm text-[var(--color-terracotta)] hover:underline"
                                    >
                                        {{ item.link }}
                                    </a>
                                    <span v-else class="text-sm text-[var(--color-warm-gray)]">—</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="edit.url(item.id)"
                                            class="rounded-lg bg-[var(--color-forest)]/10 px-3 py-2 text-sm font-medium text-[var(--color-forest)] hover:bg-[var(--color-forest)]/15"
                                        >
                                            Bearbeiten
                                        </Link>
                                        <button
                                            type="button"
                                            :disabled="deletingId === item.id"
                                            class="rounded-lg bg-red-50 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-100 disabled:opacity-50"
                                            @click="openDeleteModal(item.id)"
                                        >
                                            {{ deletingId === item.id ? 'Löschen…' : 'Löschen' }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmDeleteModal
            :open="modalOpen"
            title="Equipment löschen"
            message="Möchten Sie dieses Equipment wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden."
            delete-label="Equipment"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </AdminLayout>
</template>
