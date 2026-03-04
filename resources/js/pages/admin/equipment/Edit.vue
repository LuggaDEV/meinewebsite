<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import { index, update } from '@/routes/admin/equipment'
import Label from '@/components/ui/label/Label.vue'
import InputError from '@/components/InputError.vue'
import ImageCropResize from '@/components/admin/ImageCropResize.vue'
import type { Equipment } from '@/types/equipment'

const props = defineProps<{
    equipment: Equipment
}>()

// Vordefinierte Kategorien
const categories = [
    'Backen',
    'Kochen',
    'Messer',
    'Geschirr',
    'Küchengeräte',
    'Sonstiges'
]

const form = useForm({
    name: props.equipment.name,
    category: props.equipment.category,
    link: props.equipment.link,
    description: props.equipment.description || '',
    image: null as File | null,
})

function submit(): void {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(update.url(props.equipment.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            router.visit(index.url())
        },
    })
}
</script>

<template>
    <AdminLayout title="Equipment bearbeiten" :subtitle="equipment.name">
        <form @submit.prevent="submit" class="max-w-2xl rounded-xl border border-[var(--color-forest)]/10 bg-white p-6 shadow-sm md:p-8 space-y-6">
                    <!-- Name -->
                    <div>
                        <Label for="name">Name *</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="z.B. Kitchenaid Mixer"
                            required
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <!-- Kategorie -->
                    <div>
                        <Label for="category">Kategorie *</Label>
                        <select
                            id="category"
                            v-model="form.category"
                            class="w-full rounded-md border-[var(--color-forest)]/20 focus:border-[var(--color-forest)] focus:ring focus:ring-[var(--color-forest)]/10"
                            required
                        >
                            <option value="" disabled>Kategorie auswählen</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category" class="mt-2" />
                    </div>

                    <!-- Link -->
                    <div>
                        <Label for="link">Link (URL) *</Label>
                        <Input
                            id="link"
                            v-model="form.link"
                            type="url"
                            placeholder="https://..."
                            required
                        />
                        <InputError :message="form.errors.link" class="mt-2" />
                    </div>

                    <!-- Beschreibung (optional) -->
                    <div>
                        <Label for="description">Beschreibung (optional)</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border-[var(--color-forest)]/20 focus:border-[var(--color-forest)] focus:ring focus:ring-[var(--color-forest)]/10"
                            placeholder="Optionale Beschreibung für interne Notizen..."
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <!-- Bild Upload -->
                    <div>
                        <Label for="image">Bild</Label>
                        <div class="mt-2">
                            <ImageCropResize
                                :model-value="form.image"
                                :existing-image-url="equipment.image"
                                @update:model-value="form.image = $event"
                            />
                        </div>
                        <InputError :message="form.errors.image" class="mt-2" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-[var(--color-forest)]/10 pt-6">
                        <Link
                            :href="index.url()"
                            class="rounded-lg border border-[var(--color-forest)]/20 px-5 py-2.5 text-sm font-medium text-[var(--color-forest)] transition-colors hover:bg-[var(--color-forest)]/5"
                        >
                            Abbrechen
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-[var(--color-forest)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[var(--color-terracotta)] disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Speichern…' : 'Änderungen speichern' }}
                        </button>
                    </div>
                </form>
    </AdminLayout>
</template>
