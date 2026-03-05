<script setup lang="ts">
import { Link, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { index as adminIndex } from '@/routes/admin'
import { update } from '@/routes/admin/maintenance'

const props = defineProps<{
    enabled: boolean
    ends_at: string | null
    message: string | null
}>()

const page = usePage<{ flash?: { success?: string } }>()

const form = useForm({
    enabled: props.enabled,
    ends_at: props.ends_at
        ? props.ends_at.slice(0, 16)
        : '' as string,
    message: props.message ?? '',
})

function submit(): void {
    form.transform((data) => ({
        enabled: data.enabled,
        ends_at: data.enabled && data.ends_at ? data.ends_at : null,
        message: data.message || null,
    })).put(update.url(), { preserveScroll: true })
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
            <div v-if="form.errors.enabled" class="text-sm text-red-600">
                {{ form.errors.enabled }}
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
                <div v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                    {{ form.errors.message }}
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
                <div v-if="form.errors.ends_at" class="mt-1 text-sm text-red-600">
                    {{ form.errors.ends_at }}
                </div>
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
