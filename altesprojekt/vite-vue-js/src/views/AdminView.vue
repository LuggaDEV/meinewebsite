<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Motion } from 'motion-v'
import { getRecipes, createRecipe, updateRecipe, deleteRecipe, uploadImage } from '../services/recipeApi.js'
import { logout } from '../services/authService.js'

const router = useRouter()

const recipes = ref([])
const loading = ref(true)
const editingId = ref(null)
const message = ref({ type: '', text: '' })
const imageFile = ref(null)
const imagePreview = ref('')
const fileInput = ref(null)

const formData = ref({
  title: '',
  description: '',
  image: '',
  servings: '',
  prepTime: '',
  cookTime: '',
  ingredients: [{ amount: '', ingredient: '', notes: '' }],
  instructions: '',
})

function parseArray(text) {
  return text.split('\n').filter(line => line.trim() !== '')
}

function formatArray(arr) {
  return Array.isArray(arr) ? arr.join('\n') : ''
}

// Konvertiert String-Array von Zutaten in Objekt-Array für die Tabelle
function parseIngredientsFromArray(arr) {
  if (!Array.isArray(arr)) return []
  
  return arr.map(ingredientStr => {
    if (typeof ingredientStr === 'object' && ingredientStr.amount !== undefined) {
      // Bereits im neuen Format
      return {
        amount: ingredientStr.amount || '',
        ingredient: ingredientStr.ingredient || '',
        notes: ingredientStr.notes || ''
      }
    }
    
    // Altes Format: String parsen
    const str = String(ingredientStr).trim()
    if (!str) {
      return { amount: '', ingredient: '', notes: '' }
    }
    
    // Versuche Menge zu extrahieren (z.B. "1 kg", "200 g", "2 EL")
    // Suche nach einem Muster: Zahl gefolgt von optionaler Einheit
    const match = str.match(/^(\d+(?:\s*[.,]\d+)?\s*(?:kg|g|ml|l|EL|TL|Stk|Stück|St\.|Prise|Msp\.|Msp|Tasse|Tassen|Bund|Bünde)?)\s+(.+)$/i)
    
    if (match) {
      return {
        amount: match[1].trim(),
        ingredient: match[2].trim(),
        notes: ''
      }
    }
    
    // Fallback: Wenn kein Muster gefunden, alles als Zutat behandeln
    return {
      amount: '',
      ingredient: str,
      notes: ''
    }
  })
}

// Konvertiert Objekt-Array zurück zu String-Array für die API
function formatIngredientsForAPI(ingredients) {
  if (!Array.isArray(ingredients)) return []
  
  return ingredients
    .filter(ing => ing.ingredient && ing.ingredient.trim() !== '')
    .map(ing => {
      const parts = []
      if (ing.amount && ing.amount.trim()) {
        parts.push(ing.amount.trim())
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

function addIngredientRow() {
  formData.value.ingredients.push({
    amount: '',
    ingredient: '',
    notes: ''
  })
}

function removeIngredientRow(index) {
  formData.value.ingredients.splice(index, 1)
}

function resetForm() {
  formData.value = {
    title: '',
    description: '',
    image: '',
    servings: '',
    prepTime: '',
    cookTime: '',
    ingredients: [{ amount: '', ingredient: '', notes: '' }],
    instructions: '',
  }
  imageFile.value = null
  imagePreview.value = ''
  editingId.value = null
}

function handleImageChange(event) {
  const file = event.target.files[0]
  if (file) {
    imageFile.value = file
    // Preview erstellen
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else {
    imageFile.value = null
  }
}

function clearImage() {
  imageFile.value = null
  imagePreview.value = ''
  formData.value.image = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function startEdit(recipe) {
  editingId.value = recipe.id
  const parsedIngredients = parseIngredientsFromArray(recipe.ingredients)
  formData.value = {
    title: recipe.title || '',
    description: recipe.description || '',
    image: recipe.image || '',
    servings: recipe.servings?.toString() || '',
    prepTime: recipe.prepTime?.toString() || '',
    cookTime: recipe.cookTime?.toString() || '',
    ingredients: parsedIngredients.length > 0 ? parsedIngredients : [{ amount: '', ingredient: '', notes: '' }],
    instructions: formatArray(recipe.instructions),
  }
  imageFile.value = null
  imagePreview.value = recipe.image || ''
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function handleSubmit() {
  if (!formData.value.title || !formData.value.description) {
    message.value = { type: 'error', text: 'Titel und Beschreibung sind erforderlich.' }
    return
  }

  try {
    let imageUrl = formData.value.image

    // Wenn ein neues Bild hochgeladen wurde, zuerst Upload durchführen
    if (imageFile.value) {
      try {
        imageUrl = await uploadImage(imageFile.value)
      } catch (uploadError) {
        message.value = { type: 'error', text: uploadError.message || 'Fehler beim Hochladen des Bildes.' }
        return
      }
    }

    // Fallback auf Standard-Bild wenn kein Bild vorhanden
    if (!imageUrl) {
      imageUrl = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop'
    }

    const recipeData = {
      title: formData.value.title,
      description: formData.value.description,
      image: imageUrl,
      servings: formData.value.servings ? parseInt(formData.value.servings) : undefined,
      prepTime: formData.value.prepTime ? parseInt(formData.value.prepTime) : undefined,
      cookTime: formData.value.cookTime ? parseInt(formData.value.cookTime) : undefined,
      ingredients: formatIngredientsForAPI(formData.value.ingredients),
      instructions: parseArray(formData.value.instructions),
    }

    if (editingId.value) {
      await updateRecipe(editingId.value, recipeData)
      message.value = { type: 'success', text: 'Rezept erfolgreich aktualisiert!' }
    } else {
      await createRecipe(recipeData)
      message.value = { type: 'success', text: 'Rezept erfolgreich erstellt!' }
    }
    await loadRecipes()
    resetForm()
    setTimeout(() => {
      message.value = { type: '', text: '' }
    }, 3000)
  } catch (error) {
    message.value = { type: 'error', text: error.message || 'Fehler beim Speichern des Rezepts.' }
  }
}

async function handleDelete(id) {
  if (!confirm('Möchten Sie dieses Rezept wirklich löschen?')) {
    return
  }

  try {
    await deleteRecipe(id)
    message.value = { type: 'success', text: 'Rezept erfolgreich gelöscht!' }
    await loadRecipes()
    setTimeout(() => {
      message.value = { type: '', text: '' }
    }, 3000)
  } catch (error) {
    message.value = { type: 'error', text: error.message || 'Fehler beim Löschen des Rezepts.' }
  }
}

async function loadRecipes() {
  try {
    loading.value = true
    recipes.value = await getRecipes()
  } catch (error) {
    message.value = { type: 'error', text: 'Fehler beim Laden der Rezepte.' }
  } finally {
    loading.value = false
  }
}

async function handleLogout() {
  try {
    await logout()
    router.push('/')
  } catch (error) {
    message.value = { type: 'error', text: 'Fehler beim Abmelden.' }
  }
}

onMounted(() => {
  loadRecipes()
})
</script>

<template>
  <Motion
    :initial="{ opacity: 0 }"
    :animate="{ opacity: 1 }"
    :transition="{ duration: 0.4 }"
    class="py-12 md:py-16"
  >
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.5 }"
        class="mb-8 flex items-start justify-between"
      >
        <div>
          <h1 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] mb-2">
            Rezepte verwalten
          </h1>
          <p class="text-[var(--color-warm-gray)]">
            Hier kannst du Rezepte hinzufügen, bearbeiten oder löschen.
          </p>
        </div>
        <button
          @click="handleLogout"
          class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[var(--color-forest)] hover:text-[var(--color-terracotta)] hover:bg-[var(--color-forest)]/5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Abmelden
        </button>
      </Motion>

      <Motion
        v-if="message.text"
        :initial="{ opacity: 0, scale: 0.95 }"
        :animate="{ opacity: 1, scale: 1 }"
        :transition="{ duration: 0.3 }"
        :class="[
          'mb-6 p-4 rounded-lg',
          message.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        ]"
      >
        {{ message.text }}
      </Motion>

      <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.5, delay: 0.1 }"
        class="bg-white rounded-xl shadow-md p-6 md:p-8 mb-8"
      >
        <h2 class="font-heading text-2xl font-semibold text-[var(--color-forest)] mb-6">
          {{ editingId ? 'Rezept bearbeiten' : 'Neues Rezept hinzufügen' }}
        </h2>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                Titel *
              </label>
              <input
                v-model="formData.title"
                type="text"
                required
                class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                Bild
              </label>
              <input
                :key="`file-input-${editingId || 'new'}`"
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
                  class="mt-2 px-3 py-1 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors"
                >
                  Bild entfernen
                </button>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
              Beschreibung *
            </label>
            <textarea
              v-model="formData.description"
              required
              rows="3"
              class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
            />
          </div>

          <div class="grid md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                Portionen
              </label>
              <input
                v-model="formData.servings"
                type="number"
                min="1"
                class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                Vorbereitung (Min.)
              </label>
              <input
                v-model="formData.prepTime"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                Zubereitung (Min.)
              </label>
              <input
                v-model="formData.cookTime"
                type="number"
                min="0"
                class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
              Zutaten
            </label>
            <div class="overflow-x-auto">
              <table class="w-full border-collapse border border-[var(--color-forest)]/20 rounded-lg">
                <thead>
                  <tr class="bg-[var(--color-forest)]/5">
                    <th class="px-4 py-3 text-left text-sm font-semibold text-[var(--color-forest)] border-b border-[var(--color-forest)]/20">
                      Menge
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
                    v-for="(ingredient, index) in formData.ingredients"
                    :key="index"
                    class="hover:bg-[var(--color-forest)]/5 transition-colors"
                  >
                    <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                      <input
                        v-model="ingredient.amount"
                        type="text"
                        placeholder="z.B. 200 g"
                        class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                      />
                    </td>
                    <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                      <input
                        v-model="ingredient.ingredient"
                        type="text"
                        placeholder="z.B. Spaghetti"
                        class="w-full px-3 py-1.5 text-sm border border-[var(--color-forest)]/20 rounded focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                      />
                    </td>
                    <td class="px-4 py-2 border-b border-[var(--color-forest)]/10">
                      <input
                        v-model="ingredient.notes"
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
                        :disabled="formData.ingredients.length <= 1"
                      >
                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </td>
                  </tr>
                  <tr v-if="formData.ingredients.length === 0">
                    <td colspan="4" class="px-4 py-8 text-center text-[var(--color-warm-gray)] text-sm">
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
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">
              Zubereitungsschritte (einer pro Zeile)
            </label>
            <textarea
              v-model="formData.instructions"
              rows="8"
              placeholder="Pasta in Salzwasser al dente kochen.&#10;Guanciale würfeln und braten.&#10;..."
              class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] font-mono text-sm"
            />
          </div>

          <div class="flex gap-4">
            <button
              type="submit"
              class="px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
            >
              {{ editingId ? 'Aktualisieren' : 'Erstellen' }}
            </button>
            <button
              v-if="editingId"
              type="button"
              @click="resetForm"
              class="px-6 py-3 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
            >
              Abbrechen
            </button>
          </div>
        </form>
      </Motion>

      <Motion
        :initial="{ opacity: 0, y: 20 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.5, delay: 0.2 }"
      >
        <h2 class="font-heading text-2xl font-semibold text-[var(--color-forest)] mb-6">
          Alle Rezepte
        </h2>

        <div v-if="loading" class="text-center text-[var(--color-warm-gray)] py-8">
          Rezepte werden geladen...
        </div>

        <div v-else-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)] py-8">
          Noch keine Rezepte vorhanden.
        </div>

        <div v-else class="space-y-4">
          <Motion
            v-for="(recipe, index) in recipes"
            :key="recipe.id"
            :initial="{ opacity: 0, x: -20 }"
            :animate="{ opacity: 1, x: 0 }"
            :transition="{ duration: 0.4, delay: index * 0.05 }"
            class="bg-white rounded-lg shadow-md p-4 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4"
          >
            <div class="flex-1">
              <h3 class="font-heading text-lg font-semibold text-[var(--color-forest)] mb-1">
                {{ recipe.title }}
              </h3>
              <p class="text-sm text-[var(--color-warm-gray)] line-clamp-2">
                {{ recipe.description }}
              </p>
            </div>
            <div class="flex gap-2">
              <button
                @click="startEdit(recipe)"
                class="px-4 py-2 bg-[var(--color-terracotta)] text-white text-sm font-medium rounded-lg hover:bg-[var(--color-terracotta)]/90 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-terracotta)]"
              >
                Bearbeiten
              </button>
              <button
                @click="handleDelete(recipe.id)"
                class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600"
              >
                Löschen
              </button>
            </div>
          </Motion>
        </div>
      </Motion>
    </div>
  </Motion>
</template>
