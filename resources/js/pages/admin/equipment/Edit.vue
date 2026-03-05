<script setup lang="ts">
import { Link, router, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import { index, update, fetchFromUrl } from '@/routes/admin/equipment'
import Label from '@/components/ui/label/Label.vue'
import InputError from '@/components/InputError.vue'
import ImageCropResize from '@/components/admin/ImageCropResize.vue'
import type { Equipment } from '@/types/equipment'
import { ref } from 'vue'

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
    price: props.equipment.price || '',
    original_price: props.equipment.original_price ?? '',
    discount_percentage: props.equipment.discount_percentage ?? '',
    image: null as File | null,
    image_url: null as string | null,
})

const fetchLoading = ref(false)
const fetchError = ref<string | null>(null)

async function fillFromLink(): Promise<void> {
    const url = form.link?.trim()
    if (!url) {
        fetchError.value = 'Bitte zuerst einen Link eingeben.'
        return
    }
    try {
        new URL(url)
    } catch {
        fetchError.value = 'Bitte eine gültige URL eingeben.'
        return
    }
    fetchError.value = null
    fetchLoading.value = true
    try {
        const csrfCookie = document.cookie
            .split('; ')
            .find((row) => row.startsWith('XSRF-TOKEN='))
        const csrfToken = csrfCookie
            ? decodeURIComponent(csrfCookie.split('=')[1] ?? '')
            : ''
        const response = await fetch(fetchFromUrl.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...(csrfToken && { 'X-XSRF-TOKEN': csrfToken }),
            },
            body: JSON.stringify({ url }),
            credentials: 'same-origin',
        })
        const data = await response.json()
        if (!response.ok) {
            fetchError.value = data.message ?? 'Die Seite konnte nicht geladen werden.'
            return
        }
        if (data.name) form.name = data.name
        if (data.description) form.description = data.description
        if (data.image_url) form.image_url = data.image_url
        if (data.price) form.price = data.price
        form.original_price = data.original_price ?? ''
        form.discount_percentage = data.discount_percentage ?? ''
    } catch {
        fetchError.value = 'Die Seite konnte nicht geladen werden.'
    } finally {
        fetchLoading.value = false
    }
}

function onImageUpdate(value: File | null): void {
    form.image = value
    form.image_url = null
}

function submit(): void {
    form.transform((data) => {
        const { image_url, ...rest } = data
        const payload = image_url ? { ...rest, image_url } : rest
        return { ...payload, _method: 'PUT' }
    }).post(update.url(props.equipment.id), {
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
                    <!-- Link + Von Link ausfüllen -->
                    <div>
                        <Label for="link">Link (URL) *</Label>
                        <div class="mt-2 flex flex-col sm:flex-row gap-2">
                            <Input
                                id="link"
                                v-model="form.link"
                                type="url"
                                class="flex-1"
                                placeholder="https://..."
                                required
                            />
                            <button
                                type="button"
                                :disabled="fetchLoading"
                                class="rounded-lg border border-[var(--color-forest)]/20 bg-[var(--color-cream)] px-4 py-2.5 text-sm font-medium text-[var(--color-forest)] transition-colors hover:bg-[var(--color-forest)]/5 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                                @click="fillFromLink"
                            >
                                {{ fetchLoading ? 'Laden…' : 'Von Link ausfüllen' }}
                            </button>
                        </div>
                        <p v-if="fetchError" class="mt-2 text-sm text-red-600">
                            {{ fetchError }}
                        </p>
                        <InputError :message="form.errors.link" class="mt-2" />
                    </div>

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

                    <!-- Beschreibung (optional) -->
                    <div>
                        <Label for="description">Beschreibung (optional)</Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-md border-[var(--color-forest)]/20 focus:border-[var(--color-forest)] focus:ring focus:ring-[var(--color-forest)]/10"
                            placeholder="Kurze Beschreibung, wird auf der Equipment-Seite angezeigt."
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <!-- Preis (optional) -->
                    <div>
                        <Label for="price">Preis (optional)</Label>
                        <Input
                            id="price"
                            v-model="form.price"
                            type="text"
                            placeholder="z.B. 29,99 €"
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>

                    <!-- Bild Upload -->
                    <div>
                        <Label for="image">Bild</Label>
                        <div class="mt-2">
                            <ImageCropResize
                                :model-value="form.image"
                                :existing-image-url="form.image_url || equipment.image"
                                :key="String(!!(form.image_url || equipment.image))"
                                @update:model-value="onImageUpdate"
                            />
                        </div>
                        <InputError :message="form.errors.image ?? form.errors.image_url" class="mt-2" />
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
