import { ref } from 'vue'

const AUTH_STORAGE_KEY = 'auth'
const AUTH_TIMESTAMP_KEY = 'auth-timestamp'
const SESSION_DURATION = 24 * 60 * 60 * 1000 // 24 Stunden

// Lokale Credentials (können später konfigurierbar gemacht werden)
const ADMIN_USERNAME = 'admin'
const ADMIN_PASSWORD = 'admin'

const isAuthenticated = ref(false)

// Initialisiere Auth-Status beim Laden
function initAuth() {
  const authData = localStorage.getItem(AUTH_STORAGE_KEY)
  const authTimestamp = localStorage.getItem(AUTH_TIMESTAMP_KEY)
  
  if (authData === 'true' && authTimestamp) {
    const timestamp = parseInt(authTimestamp)
    const now = Date.now()
    
    // Prüfe, ob Session noch gültig ist
    if (now - timestamp < SESSION_DURATION) {
      isAuthenticated.value = true
    } else {
      // Session abgelaufen
      localStorage.removeItem(AUTH_STORAGE_KEY)
      localStorage.removeItem(AUTH_TIMESTAMP_KEY)
      isAuthenticated.value = false
    }
  } else {
    isAuthenticated.value = false
  }
}

// Initialisiere beim Import
initAuth()

export async function login(username, password) {
  // Lokale Authentifizierung
  if (username === ADMIN_USERNAME && password === ADMIN_PASSWORD) {
    localStorage.setItem(AUTH_STORAGE_KEY, 'true')
    localStorage.setItem(AUTH_TIMESTAMP_KEY, Date.now().toString())
    isAuthenticated.value = true
    return { success: true, message: 'Erfolgreich angemeldet' }
  } else {
    throw new Error('Ungültiger Benutzername oder Passwort')
  }
}

export async function logout() {
  localStorage.removeItem(AUTH_STORAGE_KEY)
  localStorage.removeItem(AUTH_TIMESTAMP_KEY)
  isAuthenticated.value = false
  return { success: true, message: 'Erfolgreich abgemeldet' }
}

export async function checkAuth() {
  const authData = localStorage.getItem(AUTH_STORAGE_KEY)
  const authTimestamp = localStorage.getItem(AUTH_TIMESTAMP_KEY)
  
  if (authData === 'true' && authTimestamp) {
    const timestamp = parseInt(authTimestamp)
    const now = Date.now()
    
    // Prüfe, ob Session noch gültig ist
    if (now - timestamp < SESSION_DURATION) {
      isAuthenticated.value = true
      return true
    } else {
      // Session abgelaufen
      localStorage.removeItem(AUTH_STORAGE_KEY)
      localStorage.removeItem(AUTH_TIMESTAMP_KEY)
      isAuthenticated.value = false
      return false
    }
  }
  
  isAuthenticated.value = false
  return false
}

export { isAuthenticated }
