import { compressImage, getImageUrl } from './imageService.js'

const API_BASE_URL = 'http://localhost:3000/api'
const STORAGE_KEY = 'recipes'
const BACKEND_TIMEOUT = 2000 // 2 Sekunden Timeout für Backend-Check

/**
 * Prüft, ob das Backend verfügbar ist
 */
async function checkBackendAvailable() {
  try {
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), BACKEND_TIMEOUT)
    
    const response = await fetch(`${API_BASE_URL}/health`, {
      signal: controller.signal,
      method: 'GET',
    })
    
    clearTimeout(timeoutId)
    return response.ok
  } catch (error) {
    return false
  }
}

/**
 * Lädt Rezepte aus localStorage
 */
function getRecipesFromLocalStorage() {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored) {
      return JSON.parse(stored)
    }
  } catch (error) {
    console.error('Error reading from localStorage:', error)
  }
  return []
}

/**
 * Speichert Rezepte in localStorage
 */
function saveRecipesToLocalStorage(recipes) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(recipes))
  } catch (error) {
    console.error('Error saving to localStorage:', error)
    // localStorage könnte voll sein
    if (error.name === 'QuotaExceededError') {
      throw new Error('Speicher voll. Bitte lösche einige Rezepte oder Bilder.')
    }
    throw error
  }
}

/**
 * Generiert eine eindeutige ID für ein neues Rezept
 */
function generateRecipeId() {
  const recipes = getRecipesFromLocalStorage()
  const maxId = recipes.length > 0 
    ? Math.max(...recipes.map(r => r.id || 0))
    : 0
  return maxId + 1
}

/**
 * Lädt alle Rezepte
 * Versucht zuerst Backend, fallback zu localStorage
 */
export async function getRecipes() {
  const backendAvailable = await checkBackendAvailable()
  
  if (backendAvailable) {
    try {
      const response = await fetch(`${API_BASE_URL}/recipes`)
      if (response.ok) {
        const recipes = await response.json()
        // Synchronisiere mit localStorage
        saveRecipesToLocalStorage(recipes)
        return recipes
      }
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Laden:', error)
    }
  }
  
  // Fallback zu localStorage
  return getRecipesFromLocalStorage()
}

/**
 * Lädt ein einzelnes Rezept
 */
export async function getRecipe(id) {
  const backendAvailable = await checkBackendAvailable()
  
  if (backendAvailable) {
    try {
      const response = await fetch(`${API_BASE_URL}/recipes/${id}`)
      if (response.ok) {
        return await response.json()
      }
      if (response.status === 404) {
        return null
      }
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Laden:', error)
    }
  }
  
  // Fallback zu localStorage
  const recipes = getRecipesFromLocalStorage()
  const recipe = recipes.find(r => r.id === parseInt(id))
  return recipe || null
}

/**
 * Erstellt ein neues Rezept
 */
export async function createRecipe(recipe) {
  const backendAvailable = await checkBackendAvailable()
  
  // Generiere ID falls nicht vorhanden
  if (!recipe.id) {
    recipe.id = generateRecipeId()
  }
  
  // Speichere immer in localStorage
  const recipes = getRecipesFromLocalStorage()
  recipes.push(recipe)
  saveRecipesToLocalStorage(recipes)
  
  // Optional: Auch im Backend speichern, falls verfügbar
  if (backendAvailable) {
    try {
      await fetch(`${API_BASE_URL}/recipes`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(recipe),
      })
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Speichern:', error)
      // Ignoriere Fehler - Daten sind bereits in localStorage
    }
  }
  
  return recipe
}

/**
 * Aktualisiert ein Rezept
 */
export async function updateRecipe(id, recipe) {
  const backendAvailable = await checkBackendAvailable()
  
  // Aktualisiere immer in localStorage
  const recipes = getRecipesFromLocalStorage()
  const index = recipes.findIndex(r => r.id === parseInt(id))
  
  if (index === -1) {
    throw new Error('Rezept nicht gefunden')
  }
  
  recipes[index] = { ...recipe, id: parseInt(id) }
  saveRecipesToLocalStorage(recipes)
  
  // Optional: Auch im Backend aktualisieren, falls verfügbar
  if (backendAvailable) {
    try {
      await fetch(`${API_BASE_URL}/recipes/${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(recipe),
      })
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Aktualisieren:', error)
      // Ignoriere Fehler - Daten sind bereits in localStorage aktualisiert
    }
  }
  
  return recipes[index]
}

/**
 * Löscht ein Rezept
 */
export async function deleteRecipe(id) {
  const backendAvailable = await checkBackendAvailable()
  
  // Lösche immer aus localStorage
  const recipes = getRecipesFromLocalStorage()
  const filtered = recipes.filter(r => r.id !== parseInt(id))
  
  if (filtered.length === recipes.length) {
    throw new Error('Rezept nicht gefunden')
  }
  
  saveRecipesToLocalStorage(filtered)
  
  // Optional: Auch im Backend löschen, falls verfügbar
  if (backendAvailable) {
    try {
      await fetch(`${API_BASE_URL}/recipes/${id}`, {
        method: 'DELETE',
        credentials: 'include',
      })
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Löschen:', error)
      // Ignoriere Fehler - Daten sind bereits aus localStorage gelöscht
    }
  }
  
  return true
}

/**
 * Lädt ein Bild hoch
 * Versucht zuerst Backend, fallback zu Base64
 */
export async function uploadImage(file) {
  const backendAvailable = await checkBackendAvailable()
  
  if (backendAvailable) {
    try {
      const formData = new FormData()
      formData.append('image', file)
      
      const response = await fetch(`${API_BASE_URL}/upload`, {
        method: 'POST',
        credentials: 'include',
        body: formData,
      })
      
      if (response.ok) {
        const data = await response.json()
        return `http://localhost:3000${data.url}`
      }
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Bild-Upload:', error)
      // Fallback zu Base64
    }
  }
  
  // Fallback: Konvertiere zu Base64
  try {
    const base64 = await compressImage(file)
    return base64
  } catch (error) {
    throw new Error('Fehler beim Konvertieren des Bildes: ' + error.message)
  }
}
