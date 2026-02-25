<script setup lang="ts">
import { ref, watch, onBeforeUnmount } from 'vue'
import { Cropper } from 'vue-advanced-cropper'
import 'vue-advanced-cropper/dist/style.css'
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import Input from '@/components/ui/input/Input.vue'
import Label from '@/components/ui/label/Label.vue'

const props = withDefaults(
    defineProps<{
        modelValue: File | null
        existingImageUrl?: string | null
    }>(),
    { existingImageUrl: null }
)

const emit = defineEmits<{
    'update:modelValue': [value: File | null]
}>()

const dialogOpen = ref(false)
const cropperRef = ref<InstanceType<typeof Cropper> | null>(null)
const pendingFile = ref<File | null>(null)
const cropImageSrc = ref<string>('')
const maxWidth = ref<number | ''>('')
const maxHeight = ref<number | ''>('')

const fileInputRef = ref<HTMLInputElement | null>(null)
const previewUrl = ref<string>('')

function updatePreview(): void {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value)
    }
    if (props.modelValue) {
        previewUrl.value = URL.createObjectURL(props.modelValue)
    } else if (props.existingImageUrl) {
        previewUrl.value = props.existingImageUrl
    } else {
        previewUrl.value = ''
    }
}

// When user selects a file, open dialog with cropper
function onFileChange(e: Event): void {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0]
    if (!file || !file.type.startsWith('image/')) return
    pendingFile.value = file
    if (cropImageSrc.value) URL.revokeObjectURL(cropImageSrc.value)
    cropImageSrc.value = URL.createObjectURL(file)
    dialogOpen.value = true
    target.value = ''
}

function closeDialog(): void {
    dialogOpen.value = false
    if (cropImageSrc.value) {
        URL.revokeObjectURL(cropImageSrc.value)
        cropImageSrc.value = ''
    }
    pendingFile.value = null
}

// Resize canvas to max dimensions keeping aspect ratio
function resizeCanvas(
    source: HTMLCanvasElement,
    maxW: number,
    maxH: number
): HTMLCanvasElement {
    let { width, height } = source
    if (width <= maxW && height <= maxH) return source
    const ratio = Math.min(maxW / width, maxH / height)
    width = Math.round(width * ratio)
    height = Math.round(height * ratio)
    const canvas = document.createElement('canvas')
    canvas.width = width
    canvas.height = height
    const ctx = canvas.getContext('2d')
    if (!ctx) return source
    ctx.drawImage(source, 0, 0, width, height)
    return canvas
}

function applyCrop(): void {
    const cropper = cropperRef.value
    if (!cropper?.getResult) return
    const result = cropper.getResult()
    const canvas = result.canvas
    if (!canvas) return

    const maxW = Math.max(0, Number(maxWidth.value) || 0)
    const maxH = Math.max(0, Number(maxHeight.value) || 0)

    let finalCanvas: HTMLCanvasElement = canvas
    if (maxW > 0 || maxH > 0) {
        finalCanvas = resizeCanvas(canvas, maxW || canvas.width, maxH || canvas.height)
    }

    finalCanvas.toBlob(
        (blob) => {
            if (!blob) return
            const name = pendingFile.value?.name ?? 'image.jpg'
            const file = new File([blob], name, { type: 'image/jpeg' })
            emit('update:modelValue', file)
            closeDialog()
        },
        'image/jpeg',
        0.9
    )
}

function clearImage(): void {
    emit('update:modelValue', null)
    if (fileInputRef.value) fileInputRef.value.value = ''
}

watch([() => props.modelValue, () => props.existingImageUrl], updatePreview, { immediate: true })

onBeforeUnmount(() => {
    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewUrl.value)
    }
})
</script>

<template>
    <div class="space-y-2">
        <input
            ref="fileInputRef"
            type="file"
            accept="image/*"
            class="block w-full text-sm text-[var(--color-warm-gray)] file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-[var(--color-forest)] file:text-white hover:file:bg-[var(--color-terracotta)] file:cursor-pointer cursor-pointer"
            @change="onFileChange"
        />

        <div v-if="previewUrl" class="mt-4">
            <div class="relative inline-block">
                <img
                    :src="previewUrl"
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

    <Dialog v-model:open="dialogOpen">
        <DialogContent :show-close-button="true" class="sm:max-w-[90vw] max-h-[90vh] overflow-hidden flex flex-col">
            <DialogHeader>
                <DialogTitle>Bild zuschneiden und Größe anpassen</DialogTitle>
            </DialogHeader>

            <div v-if="cropImageSrc" class="cropper-wrapper min-h-[300px] flex-1 overflow-hidden rounded-lg border bg-[var(--color-cream)]/30">
                <Cropper
                    ref="cropperRef"
                    class="cropper h-[50vmin] min-h-[280px] w-full"
                    :src="cropImageSrc"
                />
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2">
                <div>
                    <Label for="maxWidth">Max. Breite (px)</Label>
                    <Input
                        id="maxWidth"
                        v-model.number="maxWidth"
                        type="number"
                        min="0"
                        placeholder="Optional"
                        class="mt-1"
                    />
                </div>
                <div>
                    <Label for="maxHeight">Max. Höhe (px)</Label>
                    <Input
                        id="maxHeight"
                        v-model.number="maxHeight"
                        type="number"
                        min="0"
                        placeholder="Optional"
                        class="mt-1"
                    />
                </div>
            </div>

            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="closeDialog">Abbrechen</Button>
                <Button @click="applyCrop">Übernehmen</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
