<script setup lang="ts">
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { index as adminIndex } from '@/routes/admin'
import { edit, update } from '@/routes/admin/maintenance'

const props = defineProps<{
    enabled: boolean
    ends_at: string | null
    message: string | null
    background_video_url: string | null
}>()

const page = usePage<{ flash?: { success?: string } }>()

const processing = ref(false)
const errors = ref<Record<string, string>>({})

const form = ref({
    enabled: props.enabled,
    ends_at: props.ends_at ? props.ends_at.slice(0, 16) : '',
    message: props.message ?? '',
    background_video_url: props.background_video_url ?? '',
    background_video: null as File | null,
})

function getCsrfToken(): string {
    const cookie = document.cookie
        .split('; ')
        .find((row) => row.startsWith('XSRF-TOKEN='))
    if (!cookie) return ''
    return decodeURIComponent(cookie.split('=')[1] ?? '')
}

async function submit(): Promise<void> {
    processing.value = true
    errors.value = {}

    const data = form.value
    const payload = {
        enabled: data.enabled,
        ends_at: data.enabled && data.ends_at ? data.ends_at.trim() : null,
        message: data.message.trim() || null,
        background_video_url: data.background_video_url.trim() || null,
    }

    try {
        const hasFile = data.background_video instanceof File
        let response: Response

        if (hasFile) {
            const body = new FormData()
            body.append('_method', 'PUT')
            body.append('enabled', data.enabled ? '1' : '0')
            if (payload.ends_at) body.append('ends_at', payload.ends_at)
            if (payload.message) body.append('message', payload.message)
            body.append('background_video_url', payload.background_video_url ?? '')
            body.append('background_video', data.background_video as File)

            response = await fetch(update.url(), {
                method: 'POST',
                body,
                headers: {
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
        } else {
            response = await fetch(update.url(), {
                method: 'PUT',
                body: JSON.stringify(payload),
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            })
        }

        if (response.status === 413) {
            errors.value = {
                background_video:
                    'Die Datei ist zu groß. Max. 50 MB. Bei Laravel Herd: Herd-Menü → PHP → php.ini bearbeiten und upload_max_filesize sowie post_max_size z. B. auf 64M setzen, dann Herd neu starten.',
            }
            return
        }

        if (response.status === 422) {
            const json = await response.json()
            const bag = json.errors ?? {}
            const next: Record<string, string> = {}
            for (const [key, val] of Object.entries(bag)) {
                next[key] = Array.isArray(val) ? (val[0] as string) : String(val)
            }
            errors.value = next
            return
        }

        if (response.ok || response.redirected) {
            window.location.reload()
            return
        }

        errors.value = { form: 'Speichern fehlgeschlagen. Bitte Seite neu laden und erneut versuchen.' }
    } catch {
        errors.value = {
            form: 'Netzwerkfehler oder Datei zu groß (413). Bei Video-Upload: Herd-Menü → PHP → php.ini: upload_max_filesize und post_max_size auf z. B. 64M setzen, Herd neu starten.',
        }
    } finally {
        processing.value = false
    }
}
</script>

<template>
    <AdminLayout
        title="Wartung"
        subtitle="Wartungsmodus für die Website aktivieren oder deaktivieren"
    >
        <form
            @submit.prevent="submit"
            class="space-y-6 rounded-xl border border-[var(--color-forest)]/10 bg-white p-6 shadow-sm md:p-8"
        >
            <div v-if="page.props.flash?.success" class="rounded-lg bg-green-50 p-4 text-sm text-green-800">
                {{ page.props.flash.success }}
            </div>

            <div class="flex items-center gap-3">
                <input
                    id="enabled"
                    v-model="form.enabled"
                    type="checkbox"
                    class="h-5 w-5 rounded border-[var(--color-forest)]/20 text-[var(--color-forest)] focus:ring-[var(--color-terracotta)]"
                />
                <label for="enabled" class="text-sm font-medium text-[var(--color-forest)]">
                    Wartungsmodus aktiv
                </label>
            </div>
            <div v-if="errors.enabled" class="text-sm text-red-600">
                {{ errors.enabled }}
            </div>

            <div v-show="form.enabled">
                <label class="mb-2 block text-sm font-medium text-[var(--color-forest)]">
                    Hinweistext (optional)
                </label>
                <textarea
                    v-model="form.message"
                    rows="4"
                    class="w-full rounded-lg border border-[var(--color-forest)]/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                    placeholder="z. B. Wir sind in Kürze wieder für Sie da."
                />
                <div v-if="errors.message" class="mt-1 text-sm text-red-600">
                    {{ errors.message }}
                </div>
            </div>

            <div v-show="form.enabled">
                <label class="mb-2 block text-sm font-medium text-[var(--color-forest)]">
                    Wartung voraussichtlich bis (optional)
                </label>
                <input
                    v-model="form.ends_at"
                    type="datetime-local"
                    class="w-full max-w-xs rounded-lg border border-[var(--color-forest)]/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                />
                <div v-if="errors.ends_at" class="mt-1 text-sm text-red-600">
                    {{ errors.ends_at }}
                </div>
            </div>

            <div class="space-y-3 border-t border-[var(--color-forest)]/10 pt-6">
                <h3 class="text-sm font-semibold text-[var(--color-forest)]">
                    Hintergrundvideo (Wartungsseite)
                </h3>
                <p class="text-sm text-[var(--color-warm-gray)]">
                    Optional: Video-URL oder neues Video hochladen (MP4/WebM, max. 50 MB). Wird geblurrt im Hintergrund abgespielt.
                </p>
                <div>
                    <label class="mb-1 block text-sm font-medium text-[var(--color-forest)]">
                        URL
                    </label>
                    <input
                        v-model="form.background_video_url"
                        type="url"
                        placeholder="https://… oder leer lassen und Video hochladen"
                        class="w-full rounded-lg border border-[var(--color-forest)]/20 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                    />
                    <div v-if="errors.background_video_url" class="mt-1 text-sm text-red-600">
                        {{ errors.background_video_url }}
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-[var(--color-forest)]">
                        Neues Video hochladen
                    </label>
                    <input
                        type="file"
                        accept="video/mp4,video/webm"
                        class="block w-full max-w-md text-sm text-[var(--color-forest)] file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--color-forest)]/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-[var(--color-forest)] hover:file:bg-[var(--color-forest)]/20"
                        @change="(e) => { const f = (e.target as HTMLInputElement).files?.[0]; form.background_video = f ?? null }"
                    />
                    <div v-if="errors.background_video" class="mt-1 text-sm text-red-600">
                        {{ errors.background_video }}
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-[var(--color-forest)]/10 pt-6">
                <button
                    type="submit"
                    :disabled="processing"
                    class="rounded-lg bg-[var(--color-forest)] px-5 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-forest)] focus:ring-offset-2 disabled:opacity-50"
                >
                    {{ processing ? 'Wird gespeichert…' : 'Speichern' }}
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
