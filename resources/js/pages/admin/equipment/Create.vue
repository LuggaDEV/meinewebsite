<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'
import InputError from '@/components/InputError.vue'

const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string>('')

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

function handleImageChange(event: Event): void {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) {
        form.image = file
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
    }
}

function clearImage(): void {
    form.image = null
    imagePreview.value = ''
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

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
                            <input
                                ref="fileInput"
                                id="image"
                                type="file"
                                accept="image/*"
                                @change="handleImageChange"
                                class="block w-full text-sm text-[var(--color-warm-gray)]
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-medium
                                    file:bg-[var(--color-forest)] file:text-white
                                    hover:file:bg-[var(--color-terracotta)]
                                    file:cursor-pointer cursor-pointer"
                            />
                        </div>
                        <InputError :message="form.errors.image" class="mt-2" />

                        <!-- Image Preview -->
                        <div v-if="imagePreview" class="mt-4">
                            <div class="relative inline-block">
                                <img
                                    :src="imagePreview"
                                    alt="Vorschau"
                                    class="w-48 h-48 object-cover rounded-lg border border-[var(--color-forest)]/20"
                                />
                                <button
                                    type="button"
                                    @click="clearImage"
                                    class="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors"
                                    aria-label="Bild entfernen"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
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
