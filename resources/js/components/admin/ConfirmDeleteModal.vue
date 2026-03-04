<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

defineProps<{
    open: boolean
    title: string
    message: string
    deleteLabel?: string
}>()

const emit = defineEmits<{
    confirm: []
    cancel: []
}>()

function onOpenChange(open: boolean): void {
    if (!open) {
        emit('cancel')
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent :show-close-button="true">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ message }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button
                    variant="outline"
                    @click="emit('cancel')"
                >
                    Abbrechen
                </Button>
                <Button
                    variant="destructive"
                    @click="emit('confirm')"
                >
                    Löschen
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
