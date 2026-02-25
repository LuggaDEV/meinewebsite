<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { VueDraggableNext } from 'vue-draggable-next'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import ImageCropResize from '@/components/admin/ImageCropResize.vue'

const props = defineProps<{
    recipe: {
        id: number
        title: string
        description: string
        image: string | null
        servings: number | null
        prep_time: number | null
        cook_time: number | null
        rest_time: number | null
        ingredients: string[]
        instructions: string[]
    }
}>()

const imageRemoved = ref<boolean>(false)

// Verfügbare Einheiten
const units = [
    { value: '', label: 'Keine' },
    { value: 'g', label: 'g' },
    { value: 'kg', label: 'kg' },
    { value: 'ml', label: 'ml' },
    { value: 'l', label: 'l' },
    { value: 'EL', label: 'EL' },
    { value: 'TL', label: 'TL' },
    { value: 'Stk', label: 'Stk' },
    { value: 'Stück', label: 'Stück' },
    { value: 'Prise', label: 'Prise' },
    { value: 'Msp.', label: 'Msp.' },
    { value: 'Tasse', label: 'Tasse' },
    { value: 'Tassen', label: 'Tassen' },
    { value: 'Bund', label: 'Bund' },
    { value: 'Bünde', label: 'Bünde' },
]

// Konvertiert String-Array von Zutaten in Objekt-Array für die Tabelle
function parseIngredientsFromArray(arr: string[]): Array<{ amount: string; unit: string; ingredient: string; notes: string }> {
    if (!Array.isArray(arr) || arr.length === 0) {
        return [{ amount: '', unit: '', ingredient: '', notes: '' }]
    }

    return arr.map((ingredientStr) => {
        if (typeof ingredientStr === 'object' && 'amount' in ingredientStr) {
            // Bereits im neuen Format
            return {
                amount: (ingredientStr as any).amount || '',
                unit: (ingredientStr as any).unit || '',
                ingredient: (ingredientStr as any).ingredient || '',
                notes: (ingredientStr as any).notes || '',
            }
        }

        // Altes Format: String parsen
        const str = String(ingredientStr).trim()
        if (!str) {
            return { amount: '', unit: '', ingredient: '', notes: '' }
        }

        // Versuche Menge zu extrahieren (z.B. "1 kg", "200 g", "2 EL")
        // Zuerst versuchen, Einheit am Ende der Menge zu finden
        const unitPattern = '(kg|g|ml|l|EL|TL|Stk|Stück|St\\.|Prise|Msp\\.|Msp|Tasse|Tassen|Bund|Bünde)'
        const match = str.match(new RegExp(`^(\\d+(?:\\s*[.,]\\d+)?)\\s*${unitPattern}?\\s+(.+)$`, 'i'))

        if (match) {
            return {
                amount: match[1].trim(),
                unit: match[2] || '',
                ingredient: match[3].trim(),
                notes: '',
            }
        }

        // Versuche auch ohne Zutat (nur Menge + Einheit)
        const amountOnlyMatch = str.match(new RegExp(`^(\\d+(?:\\s*[.,]\\d+)?)\\s*${unitPattern}$`, 'i'))
        if (amountOnlyMatch) {
            return {
                amount: amountOnlyMatch[1].trim(),
                unit: amountOnlyMatch[2] || '',
                ingredient: '',
                notes: '',
            }
        }

        // Fallback: Wenn kein Muster gefunden, alles als Zutat behandeln
        return {
            amount: '',
            unit: '',
            ingredient: str,
            notes: '',
        }
    })
}

// Konvertiert Objekt-Array zurück zu String-Array für die API
function formatIngredientsForAPI(ingredients: Array<{ amount: string; unit: string; ingredient: string; notes: string }>): string[] {
    if (!Array.isArray(ingredients)) {
        return []
    }

    return ingredients
        .filter((ing) => ing.ingredient && ing.ingredient.trim() !== '')
        .map((ing) => {
            const parts: string[] = []
            if (ing.amount && ing.amount.trim()) {
                const amountWithUnit = ing.unit ? `${ing.amount.trim()} ${ing.unit}` : ing.amount.trim()
                parts.push(amountWithUnit)
            }
            if (ing.ingredient && ing.ingredient.trim()) {
                parts.push(ing.ingredient.trim())
            }
            if (ing.notes && ing.notes.trim()) {
                parts.push(`(${ing.notes.trim()})`)
            }
            return parts.join(' ')
        })
}

const parsedIngredients = parseIngredientsFromArray(props.recipe.ingredients)

// Konvertiere Minuten zu Stunden/Minuten für die Anzeige
function convertMinutesToDisplay(minutes: number | null): { value: number | null; unit: 'min' | 'h' } {
    if (!minutes || minutes === 0) {
        return { value: null, unit: 'min' }
    }
    if (minutes >= 60 && minutes % 60 === 0) {
        return { value: minutes / 60, unit: 'h' }
    }
    return { value: minutes, unit: 'min' }
}

// Konvertiere Stunden/Minuten zurück zu Minuten
function convertDisplayToMinutes(value: number, unit: 'min' | 'h'): number | null {
    if (!value || value === 0) {
        return null
    }
    return unit === 'h' ? value * 60 : value
}

const prepTimeDisplay = convertMinutesToDisplay(props.recipe.prep_time)
const cookTimeDisplay = convertMinutesToDisplay(props.recipe.cook_time)
const restTimeDisplay = convertMinutesToDisplay(props.recipe.rest_time)

const form = useForm({
    title: props.recipe.title,
    description: props.recipe.description,
    image: null as File | null,
    servings: props.recipe.servings,
    prep_time_value: prepTimeDisplay.value as number | null,
    prep_time_unit: prepTimeDisplay.unit,
    cook_time_value: cookTimeDisplay.value as number | null,
    cook_time_unit: cookTimeDisplay.unit,
    rest_time_value: restTimeDisplay.value as number | null,
    rest_time_unit: restTimeDisplay.unit,
    ingredients: parsedIngredients.length > 0 ? parsedIngredients : [{ amount: '', unit: '', ingredient: '', notes: '' }],
    instructions: props.recipe.instructions.length > 0 ? [...props.recipe.instructions] : [''],
})

function onImageUpdate(value: File | null): void {
    form.image = value
    if (value === null && props.recipe.image) {
        imageRemoved.value = true
    } else if (value !== null) {
        imageRemoved.value = false
    }
}

function addIngredientRow(): void {
    form.ingredients.push({ amount: '', unit: '', ingredient: '', notes: '' })
}

function removeIngredientRow(index: number): void {
    form.ingredients.splice(index, 1)
}

function addInstructionRow(): void {
    form.instructions.push('')
}

function removeInstructionRow(index: number): void {
    form.instructions.splice(index, 1)
}


function submit(): void {
    // Konvertiere Zutaten-Objekte zu String-Array
    const formattedIngredients = formatIngredientsForAPI(form.ingredients)
    const filteredInstructions = form.instructions.filter((inst) => inst.trim() !== '')
    
    // Konvertiere Stunden/Minuten zurück zu Minuten
    const prepTime = convertDisplayToMinutes(form.prep_time_value, form.prep_time_unit)
    const cookTime = convertDisplayToMinutes(form.cook_time_value, form.cook_time_unit)
    const restTime = convertDisplayToMinutes(form.rest_time_value, form.rest_time_unit)

    form.transform((data) => {
        const transformed: any = {
            ...data,
            prep_time: prepTime,
            cook_time: cookTime,
            rest_time: restTime,
            ingredients: formattedIngredients,
            instructions: filteredInstructions,
        }
        // Wenn Bild entfernt wurde, explizit null senden
        if (imageRemoved.value) {
            transformed.image = null
        } else if (!form.image) {
            // Wenn kein neues Bild ausgewählt wurde, Feld nicht senden (altes Bild bleibt)
            delete transformed.image
        }
        return transformed
    }).put(`/admin/recipes/${props.recipe.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            router.visit('/admin/recipes')
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
                        Rezept bearbeiten
                    </h1>
                </div>

                <form @submit.prevent="submit" class="bg-white rounded-xl shadow-md p-6 md:p-8 space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
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
                            <ImageCropResize
                                :model-value="form.image"
                                :existing-image-url="recipe.image"
                                @update:model-value="onImageUpdate"
                            />
                            <div v-if="form.errors.image" class="mt-1 text-sm text-red-600">
                                {{ form.errors.image }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            Beschreibung *
                        </label>
                        <textarea
                            v-model="form.description"
                            required
                            rows="4"
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                        />
                        <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                                Portionen
                            </label>
                            <input
                                v-model.number="form.servings"
                                type="number"
                                min="1"
                                class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                                Vorbereitungszeit
                            </label>
                            <div class="flex gap-2">
                                <input
                                    :value="form.prep_time_value ?? ''"
                                    @input="form.prep_time_value = ($event.target as HTMLInputElement).value === '' ? null : Number(($event.target as HTMLInputElement).value)"
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    placeholder="0"
                                    class="flex-1 px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                />
                                <select
                                    v-model="form.prep_time_unit"
                                    class="w-24 px-3 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] bg-white"
                                >
                                    <option value="min">Min.</option>
                                    <option value="h">Std.</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                                Backzeit / Kochzeit
                            </label>
                            <div class="flex gap-2">
                                <input
                                    :value="form.cook_time_value ?? ''"
                                    @input="form.cook_time_value = ($event.target as HTMLInputElement).value === '' ? null : Number(($event.target as HTMLInputElement).value)"
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    placeholder="0"
                                    class="flex-1 px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                />
                                <select
                                    v-model="form.cook_time_unit"
                                    class="w-24 px-3 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] bg-white"
                                >
                                    <option value="min">Min.</option>
                                    <option value="h">Std.</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                                Ruhezeit
                            </label>
                            <div class="flex gap-2">
                                <input
                                    :value="form.rest_time_value ?? ''"
                                    @input="form.rest_time_value = ($event.target as HTMLInputElement).value === '' ? null : Number(($event.target as HTMLInputElement).value)"
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    placeholder="0"
                                    class="flex-1 px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                />
                                <select
                                    v-model="form.rest_time_unit"
                                    class="w-24 px-3 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] bg-white"
                                >
                                    <option value="min">Min.</option>
                                    <option value="h">Std.</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            Zutaten *
                        </label>
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-[var(--color-forest)]/20 rounded-lg">
                                <thead>
                                    <tr class="bg-[var(--color-forest)]/5">
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20">
                                            Menge
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20 w-32">
                                            Einheit
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20">
                                            Zutat
                                        </th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20">
                                            Notizen
                                        </th>
                                        <th class="px-4 py-3 text-center text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20 w-24">
                                            Aktion
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(ingredient, index) in form.ingredients"
                                        :key="index"
                                        class="hover:bg-[var(--color-forest)]/5 transition-colors"
                                    >
                                        <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                                            <input
                                                v-model="form.ingredients[index].amount"
                                                type="text"
                                                placeholder="z.B. 200"
                                                class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            />
                                        </td>
                                        <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                                            <select
                                                v-model="form.ingredients[index].unit"
                                                class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] bg-white"
                                            >
                                                <option
                                                    v-for="unitOption in units"
                                                    :key="unitOption.value"
                                                    :value="unitOption.value"
                                                >
                                                    {{ unitOption.label }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                                            <input
                                                v-model="form.ingredients[index].ingredient"
                                                type="text"
                                                placeholder="z.B. Spaghetti"
                                                class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            />
                                        </td>
                                        <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                                            <input
                                                v-model="form.ingredients[index].notes"
                                                type="text"
                                                placeholder="z.B. optional"
                                                class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                                            />
                                        </td>
                                        <td class="px-4 py-2 border-b border-[var(--color-forest)]/10 text-center">
                                            <button
                                                type="button"
                                                @click="removeIngredientRow(index)"
                                                class="px-3 py-1.5 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors focus:outline-none focus:ring-2 focus:ring-red-600"
                                                :disabled="form.ingredients.length <= 1"
                                            >
                                                <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="form.ingredients.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-center text-[var(--color-warm-gray)] text-sm">
                                            Noch keine Zutaten hinzugefügt. Klicke auf "Zeile hinzufügen".
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button
                            type="button"
                            @click="addIngredientRow"
                            class="mt-3 px-4 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-forest)]/10 hover:bg-[var(--color-forest)]/20 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                        >
                            + Zeile hinzufügen
                        </button>
                        <div v-if="form.errors.ingredients" class="mt-1 text-sm text-red-600">
                            {{ form.errors.ingredients }}
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-[var(--color-forest)]">
                                Zubereitung *
                            </label>
                            <button
                                type="button"
                                @click="addInstructionRow"
                                class="px-4 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-forest)]/10 hover:bg-[var(--color-forest)]/20 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                            >
                                + Schritt hinzufügen
                            </button>
                        </div>
                        <div v-if="form.instructions.length === 0" class="text-center py-8 text-[var(--color-warm-gray)] text-sm border border-[var(--color-forest)]/20 rounded-lg">
                            Noch keine Zubereitungsschritte hinzugefügt. Klicke auf "+ Schritt hinzufügen".
                        </div>
                        <VueDraggableNext
                            v-else
                            v-model="form.instructions"
                            :animation="200"
                            handle=".drag-handle"
                            class="space-y-4"
                        >
                            <div
                                v-for="(instruction, index) in form.instructions"
                                :key="index"
                                class="bg-white border border-[var(--color-forest)]/20 rounded-lg p-4 hover:shadow-md transition-shadow cursor-move"
                            >
                                <div class="flex gap-4 items-start">
                                    <div class="flex flex-col items-center gap-2 flex-shrink-0">
                                        <div class="drag-handle cursor-grab active:cursor-grabbing flex items-center justify-center w-10 h-10 rounded-full bg-[var(--color-terracotta)] text-white hover:bg-[var(--color-terracotta)]/90 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                            </svg>
                                        </div>
                                        <span class="text-xs text-[var(--color-warm-gray)] font-medium">
                                            {{ index + 1 }}
                                        </span>
                                    </div>
                                    <div class="flex-1">
                                        <textarea
                                            v-model="form.instructions[index]"
                                            :placeholder="`Beschreibe Schritt ${index + 1}...`"
                                            rows="3"
                                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] resize-y min-h-[80px]"
                                        />
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button
                                            v-if="form.instructions.length > 1"
                                            type="button"
                                            @click="removeInstructionRow(index)"
                                            class="p-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-600"
                                            title="Schritt löschen"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </VueDraggableNext>
                        <div v-if="form.errors.instructions" class="mt-1 text-sm text-red-600">
                            {{ form.errors.instructions }}
                        </div>
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)] disabled:opacity-50"
                        >
                            {{ form.processing ? 'Wird gespeichert...' : 'Rezept aktualisieren' }}
                        </button>
                        <button
                            type="button"
                            @click="router.visit('/admin/recipes')"
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
