/**
 * Instagram API Service
 * Lädt Instagram-Bilder für den Feed
 */

const API_BASE_URL = 'http://localhost:3000/api'
const STORAGE_KEY = 'instagram-feed'
const CACHE_DURATION = 60 * 60 * 1000 // 1 Stunde

/**
 * Prüft, ob das Backend verfügbar ist
 */
async function checkBackendAvailable() {
  try {
    const controller = new AbortController()
    const timeoutId = setTimeout(() => controller.abort(), 2000)
    
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
 * Lädt Instagram-Bilder aus localStorage
 */
function getInstagramFromLocalStorage() {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (stored) {
      const data = JSON.parse(stored)
      // Prüfe, ob Cache noch gültig ist
      if (data.timestamp && Date.now() - data.timestamp < CACHE_DURATION) {
        return data.images || []
      }
    }
  } catch (error) {
    console.error('Error reading Instagram from localStorage:', error)
  }
  return []
}

/**
 * Speichert Instagram-Bilder in localStorage
 */
function saveInstagramToLocalStorage(images) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({
      images,
      timestamp: Date.now()
    }))
  } catch (error) {
    console.error('Error saving Instagram to localStorage:', error)
  }
}

/**
 * Lädt Instagram-Bilder
 * Versucht zuerst Backend, fallback zu localStorage
 */
export async function getInstagramFeed() {
  const backendAvailable = await checkBackendAvailable()
  
  if (backendAvailable) {
    try {
      const response = await fetch(`${API_BASE_URL}/instagram`)
      if (response.ok) {
        const data = await response.json()
        // Speichere in localStorage als Cache
        if (data.images && data.images.length > 0) {
          saveInstagramToLocalStorage(data.images)
          return data.images
        }
      }
    } catch (error) {
      console.warn('Backend verfügbar, aber Fehler beim Laden:', error)
    }
  }
  
  // Fallback zu localStorage
  return getInstagramFromLocalStorage()
}

/**
 * Setzt Instagram-Bilder manuell (für Admin)
 */
export function setInstagramFeed(images) {
  saveInstagramToLocalStorage(images)
}
