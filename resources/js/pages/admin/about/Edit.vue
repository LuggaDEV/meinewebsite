<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'

const props = defineProps<{
    about: {
        id?: number
        title: string
        content: string
        image: string | null
    } | null
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string>(props.about?.image || '')
const imageRemoved = ref<boolean>(false)

const form = useForm({
    title: props.about?.title || 'Über mich',
    content: props.about?.content || '',
    image: null as File | null,
})

function handleImageChange(event: Event): void {
    const target = event.target as HTMLInputElement
    const file = target.files?.[0]
    if (file) {
        form.image = file
        imageRemoved.value = false
        const reader = new FileReader()
        reader.onload = (e) => {
            imagePreview.value = e.target.result as string
        }
        reader.readAsDataURL(file)
    }
}

function clearImage(): void {
    form.image = null
    imagePreview.value = ''
    imageRemoved.value = true
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}

function submit(): void {
    form.transform((data) => {
        const transformed: any = { ...data }
        // Wenn Bild entfernt wurde, explizit null senden
        if (imageRemoved.value) {
            transformed.image = null
        } else if (!form.image) {
            // Wenn kein neues Bild ausgewählt wurde, Feld nicht senden (altes Bild bleibt)
            delete transformed.image
        }
        return transformed
    }).put('/admin/about', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            // Bild-Preview aktualisieren falls vorhanden
            if (props.about?.image && !imageRemoved.value && !form.image) {
                imagePreview.value = props.about.image
            }
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
                        Über Mich Sektion bearbeiten
                    </h1>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md p-6 md:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            Titel *
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                        />
                        <div v-if="form.errors.title" class="mt-1 text-sm text-red-600">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            Bild
                        </label>
                        <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            @change="handleImageChange"
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[var(--color-forest)] file:text-white hover:file:bg-[var(--color-terracotta)] file:cursor-pointer"
                        />
                        <div v-if="imagePreview" class="mt-4">
                            <img
                                :src="imagePreview"
                                alt="Vorschau"
                                class="w-full max-w-xs h-48 object-cover rounded-lg border border-[var(--color-forest)]/20"
                            />
                            <button
                                type="button"
                                @click="clearImage"
                                class="mt-2 text-sm text-red-600 hover:text-red-700"
                            >
                                Bild entfernen
                            </button>
                        </div>
                        <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                            {{ form.errors.image }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            Inhalt *
                        </label>
                        <textarea
                            v-model="form.content"
                            required
                            rows="12"
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                            placeholder="Beschreiben Sie sich hier..."
                        />
                        <div v-if="form.errors.content" class="mt-1 text-sm text-red-600">
                            {{ form.errors.content }}
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)] disabled:opacity-50"
                        >
                            {{ form.processing ? 'Wird gespeichert...' : 'Speichern' }}
                        </button>
                        <button
                            type="button"
                            @click="router.visit('/admin')"
                            class="px-6 py-3 text-[var(--color-forest)] font-medium rounded-lg hover:bg-[var(--color-forest)]/5 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                        >
                            Abbrechen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </RecipeLayout>
</template>
