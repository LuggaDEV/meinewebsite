/**
 * Bildverwaltung mit Base64-Konvertierung als Fallback
 */

/**
 * Konvertiert eine Bild-Datei zu einem Base64-String
 * @param {File} file - Die Bild-Datei
 * @returns {Promise<string>} Base64-String mit data-URL
 */
export function convertFileToBase64(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    
    reader.onload = () => {
      resolve(reader.result)
    }
    
    reader.onerror = () => {
      reject(new Error('Fehler beim Konvertieren des Bildes'))
    }
    
    reader.readAsDataURL(file)
  })
}

/**
 * Komprimiert ein Bild, falls es zu groß ist
 * @param {File} file - Die Bild-Datei
 * @param {number} maxSizeKB - Maximale Größe in KB (Standard: 500KB)
 * @returns {Promise<string>} Base64-String (komprimiert falls nötig)
 */
export async function compressImage(file, maxSizeKB = 500) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    
    reader.onload = (e) => {
      const img = new Image()
      
      img.onload = () => {
        const canvas = document.createElement('canvas')
        let width = img.width
        let height = img.height
        let quality = 0.9
        
        // Berechne neue Dimensionen falls nötig
        const maxDimension = 1920
        if (width > maxDimension || height > maxDimension) {
          if (width > height) {
            height = (height / width) * maxDimension
            width = maxDimension
          } else {
            width = (width / height) * maxDimension
            height = maxDimension
          }
        }
        
        canvas.width = width
        canvas.height = height
        
        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)
        
        // Versuche Komprimierung
        const compress = () => {
          const dataUrl = canvas.toDataURL('image/jpeg', quality)
          const sizeKB = (dataUrl.length * 3) / 4 / 1024
          
          if (sizeKB <= maxSizeKB || quality <= 0.1) {
            resolve(dataUrl)
          } else {
            quality -= 0.1
            compress()
          }
        }
        
        compress()
      }
      
      img.onerror = () => {
        reject(new Error('Fehler beim Laden des Bildes'))
      }
      
      img.src = e.target.result
    }
    
    reader.onerror = () => {
      reject(new Error('Fehler beim Lesen der Datei'))
    }
    
    reader.readAsDataURL(file)
  })
}

/**
 * Gibt die URL für ein Bild zurück (Backend-URL oder data-URL)
 * @param {string} imageData - Backend-URL oder Base64-String
 * @returns {string} Bild-URL
 */
export function getImageUrl(imageData) {
  if (!imageData) {
    return 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&h=400&fit=crop'
  }
  
  // Wenn bereits eine vollständige URL (Backend oder extern)
  if (imageData.startsWith('http://') || imageData.startsWith('https://')) {
    return imageData
  }
  
  // Wenn Base64-String
  if (imageData.startsWith('data:')) {
    return imageData
  }
  
  // Fallback
  return imageData
}
