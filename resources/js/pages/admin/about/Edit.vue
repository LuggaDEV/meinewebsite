<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { index as adminIndex } from '@/routes/admin'
import { update } from '@/routes/admin/about'

type CareerFormRow = {
    organization: string
    role: string
    period: string
    location: string
}

function normalizeCareerRows(
    rows:
        | Array<{
              organization?: string
              role?: string
              period?: string
              location?: string | null
          }>
        | null
        | undefined,
): CareerFormRow[] {
    if (!rows?.length) {
        return []
    }

    return rows.map((r) => ({
        organization: r.organization ?? '',
        role: r.role ?? '',
        period: r.period ?? '',
        location: r.location ?? '',
    }))
}

const props = defineProps<{
    about: {
        id?: number
        title: string
        content: string
        image: string | null
        career_timeline?: CareerFormRow[] | null
    } | null
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const imagePreview = ref<string>(props.about?.image || '')
const imageRemoved = ref<boolean>(false)

const form = useForm({
    title: props.about?.title || 'Über mich',
    content: props.about?.content || '',
    image: null as File | null,
    career_timeline: normalizeCareerRows(props.about?.career_timeline ?? null),
})

function addCareerRow(): void {
    form.career_timeline.push({
        organization: '',
        role: '',
        period: '',
        location: '',
    })
}

function removeCareerRow(index: number): void {
    form.career_timeline.splice(index, 1)
}

function careerFieldError(index: number, field: keyof CareerFormRow): string | undefined {
    const key = `career_timeline.${index}.${field}` as const
    return (form.errors as Record<string, string | undefined>)[key]
}

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
        const transformed: Record<string, unknown> = { ...data }
        if (imageRemoved.value) {
            transformed.remove_image = true
            delete transformed.image
        } else if (!form.image) {
            delete transformed.image
        }
        return transformed
    }).put(update.url(), {
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
    <AdminLayout title="Über Mich" subtitle="Inhalt der „Über mich“-Sektion bearbeiten">
        <form @submit.prevent="submit" class="rounded-xl border border-[var(--color-forest)]/10 bg-white p-6 shadow-sm md:p-8 space-y-6">
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

                    <div class="border-t border-[var(--color-forest)]/10 pt-6">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-[var(--color-forest)]">Berufserfahrung (Zeitachse)</h3>
                                <p class="mt-1 text-xs text-[var(--color-forest)]/70">
                                    Leere Zeilen werden beim Speichern ignoriert. Nur vollständig ausgefüllte Stationen erscheinen auf der
                                    Website.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 rounded-lg border border-[var(--color-forest)]/25 px-3 py-2 text-sm font-medium text-[var(--color-forest)] transition-colors hover:bg-[var(--color-forest)]/5"
                                @click="addCareerRow"
                            >
                                Station hinzufügen
                            </button>
                        </div>

                        <div v-if="form.career_timeline.length === 0" class="rounded-lg border border-dashed border-[var(--color-forest)]/20 bg-[var(--color-forest)]/[0.03] px-4 py-6 text-center text-sm text-[var(--color-forest)]/70">
                            Noch keine Stationen. Klicke auf „Station hinzufügen“, um eine Zeitachse aufzubauen.
                        </div>

                        <ul v-else class="space-y-4">
                            <li
                                v-for="(row, index) in form.career_timeline"
                                :key="index"
                                class="rounded-lg border border-[var(--color-forest)]/15 bg-slate-50/80 p-4"
                            >
                                <div class="mb-3 flex items-center justify-between gap-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-[var(--color-forest)]/60">
                                        Station {{ index + 1 }}
                                    </span>
                                    <button
                                        type="button"
                                        class="text-sm text-red-600 hover:text-red-700"
                                        @click="removeCareerRow(index)"
                                    >
                                        Entfernen
                                    </button>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-[var(--color-forest)]">Restaurant / Hotel *</label>
                                        <input
                                            v-model="row.organization"
                                            type="text"
                                            class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            placeholder="z. B. Oud Sluis"
                                        />
                                        <div v-if="careerFieldError(index, 'organization')" class="mt-1 text-xs text-red-600">
                                            {{ careerFieldError(index, 'organization') }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-[var(--color-forest)]">Rolle / Position *</label>
                                        <input
                                            v-model="row.role"
                                            type="text"
                                            class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            placeholder="z. B. Commis de Cuisine"
                                        />
                                        <div v-if="careerFieldError(index, 'role')" class="mt-1 text-xs text-red-600">
                                            {{ careerFieldError(index, 'role') }}
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-[var(--color-forest)]">Zeitraum *</label>
                                        <input
                                            v-model="row.period"
                                            type="text"
                                            class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            placeholder="z. B. 2019 – 2021"
                                        />
                                        <div v-if="careerFieldError(index, 'period')" class="mt-1 text-xs text-red-600">
                                            {{ careerFieldError(index, 'period') }}
                                        </div>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="mb-1 block text-xs font-medium text-[var(--color-forest)]">Ort (optional)</label>
                                        <input
                                            v-model="row.location"
                                            type="text"
                                            class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            placeholder="z. B. Sluis, NL"
                                        />
                                        <div v-if="careerFieldError(index, 'location')" class="mt-1 text-xs text-red-600">
                                            {{ careerFieldError(index, 'location') }}
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-wrap gap-3 border-t border-[var(--color-forest)]/10 pt-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-[var(--color-forest)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-forest)] focus:ring-offset-2 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Wird gespeichert…' : 'Speichern' }}
                        </button>
                        <Link
                            :href="adminIndex.url()"
                            class="rounded-lg border border-[var(--color-forest)]/20 px-5 py-2.5 text-sm font-medium text-[var(--color-forest)] transition-colors hover:bg-[var(--color-forest)]/5 focus:outline-none focus:ring-2 focus:ring-[var(--color-forest)] focus:ring-offset-2"
                        >
                            Abbrechen
                        </Link>
                    </div>
                </form>
    </AdminLayout>
</template>
