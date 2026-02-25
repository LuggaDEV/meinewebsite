<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import InputError from '@/components/InputError.vue'
import ImageCropResize from '@/components/admin/ImageCropResize.vue'

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
    name: '',
    category: '',
    link: '',
    description: '',
    image: null as File | null,
})

function submit(): void {
    form.post('/admin/equipment', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/admin/equipment')
        },
    })
}
</script>

<template>
    <RecipeLayout>
        <div class="py-12 md:py-16">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h1 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] mb-2">
                        Neues Equipment hinzufügen
                    </h1>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md border border-[var(--color-forest)]/5 p-6 md:p-8 space-y-6">
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
                        <Label for="image">Bild (optional)</Label>
                        <div class="mt-2">
                            <ImageCropResize
                                :model-value="form.image"
                                @update:model-value="form.image = $event"
                            />
                        </div>
                        <InputError :message="form.errors.image" class="mt-2" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-[var(--color-forest)]/10">
                        <button
                            type="button"
                            @click="router.visit('/admin/equipment')"
                            class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg border-2 border-[var(--color-forest)]/20 hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 transition-colors"
                        >
                            Abbrechen
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ form.processing ? 'Speichern...' : 'Equipment hinzufügen' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </RecipeLayout>
</template>
