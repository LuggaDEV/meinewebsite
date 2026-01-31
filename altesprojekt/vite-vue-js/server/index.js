import express from 'express'
import cors from 'cors'
import cookieParser from 'cookie-parser'
import session from 'express-session'
import multer from 'multer'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const app = express()
const PORT = 3000
const RECIPES_FILE = path.join(__dirname, 'recipes.json')
const UPLOADS_DIR = path.join(__dirname, 'uploads')

// Uploads-Ordner erstellen falls nicht vorhanden
if (!fs.existsSync(UPLOADS_DIR)) {
  fs.mkdirSync(UPLOADS_DIR, { recursive: true })
}

// Multer-Konfiguration
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, UPLOADS_DIR)
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9)
    const ext = path.extname(file.originalname)
    cb(null, 'recipe-' + uniqueSuffix + ext)
  },
})

const upload = multer({
  storage: storage,
  limits: {
    fileSize: 5 * 1024 * 1024, // 5MB
  },
  fileFilter: (req, file, cb) => {
    const allowedExtensions = /\.(jpeg|jpg|png|gif|webp)$/i
    // MIME-Types: image/jpeg (für .jpg und .jpeg), image/png, image/gif, image/webp
    const allowedMimeTypes = /^image\/(jpeg|png|gif|webp)$/i
    
    const extname = allowedExtensions.test(path.extname(file.originalname))
    const mimetype = allowedMimeTypes.test(file.mimetype)
    
    if (extname && mimetype) {
      cb(null, true)
    } else {
      console.error('File rejected:', {
        filename: file.originalname,
        mimetype: file.mimetype,
        extname: path.extname(file.originalname)
      })
      cb(new Error('Nur Bilddateien sind erlaubt (jpeg, jpg, png, gif, webp). Erhalten: ' + file.mimetype))
    }
  },
})

// Credentials (später optional in Config-Datei)
const ADMIN_USERNAME = 'admin'
const ADMIN_PASSWORD = 'password'

app.use(cors({
  origin: (origin, callback) => {
    // Erlaube alle localhost-Origins für Entwicklung
    if (!origin || origin.startsWith('http://localhost:') || origin.startsWith('http://127.0.0.1:')) {
      callback(null, true)
    } else {
      callback(new Error('Nicht erlaubt durch CORS'))
    }
  },
  credentials: true,
}))
app.use(cookieParser())
app.use(express.json())
app.use(session({
  secret: 'your-secret-key-change-in-production',
  resave: false,
  saveUninitialized: false,
  cookie: {
    secure: false,
    httpOnly: true,
    maxAge: 24 * 60 * 60 * 1000,
  },
}))

function requireAuth(req, res, next) {
  if (req.session && req.session.isAuthenticated) {
    next()
  } else {
    res.status(401).json({ error: 'Nicht autorisiert' })
  }
}

function readRecipes() {
  try {
    const data = fs.readFileSync(RECIPES_FILE, 'utf8')
    return JSON.parse(data)
  } catch (error) {
    console.error('Error reading recipes:', error)
    return []
  }
}

function writeRecipes(recipes) {
  try {
    fs.writeFileSync(RECIPES_FILE, JSON.stringify(recipes, null, 2), 'utf8')
    return true
  } catch (error) {
    console.error('Error writing recipes:', error)
    return false
  }
}

app.get('/api/recipes', (req, res) => {
  const recipes = readRecipes()
  res.json(recipes)
})

app.get('/api/recipes/:id', (req, res) => {
  const recipes = readRecipes()
  const id = parseInt(req.params.id)
  const recipe = recipes.find(r => r.id === id)
  
  if (recipe) {
    res.json(recipe)
  } else {
    res.status(404).json({ error: 'Rezept nicht gefunden' })
  }
})

app.post('/api/login', (req, res) => {
  const { username, password } = req.body
  
  if (username === ADMIN_USERNAME && password === ADMIN_PASSWORD) {
    req.session.isAuthenticated = true
    req.session.username = username
    res.json({ success: true, message: 'Erfolgreich eingeloggt' })
  } else {
    res.status(401).json({ error: 'Ungültige Anmeldedaten' })
  }
})

app.post('/api/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      return res.status(500).json({ error: 'Fehler beim Abmelden' })
    }
    res.json({ success: true, message: 'Erfolgreich abgemeldet' })
  })
})

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', message: 'Server läuft' })
})

// Instagram Feed Endpoint
// TODO: Hier kann später die Instagram API integriert werden
// Für jetzt: Gibt leeres Array zurück, Frontend nutzt localStorage
app.get('/api/instagram', async (req, res) => {
  try {
    // Platzhalter für zukünftige Instagram API Integration
    // Beispiel mit Instagram Basic Display API:
    // const accessToken = process.env.INSTAGRAM_ACCESS_TOKEN
    // const response = await fetch(`https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,permalink,thumbnail_url&access_token=${accessToken}`)
    // const data = await response.json()
    // const images = data.data.map(post => ({
    //   url: post.media_url,
    //   link: post.permalink,
    //   caption: post.caption
    // }))
    
    // Für jetzt: Leeres Array zurückgeben
    res.json({ images: [] })
  } catch (error) {
    console.error('Error fetching Instagram feed:', error)
    res.status(500).json({ error: 'Fehler beim Laden des Instagram-Feeds' })
  }
})

app.get('/api/auth/check', (req, res) => {
  if (req.session && req.session.isAuthenticated) {
    res.json({ authenticated: true })
  } else {
    res.json({ authenticated: false })
  }
})

// Statische Dateien aus uploads-Ordner servieren
app.use('/uploads', express.static(UPLOADS_DIR))

// Upload-Endpoint
app.post('/api/upload', requireAuth, upload.single('image'), (req, res) => {
  if (!req.file) {
    return res.status(400).json({ error: 'Keine Datei hochgeladen' })
  }
  
  const fileUrl = `/uploads/${req.file.filename}`
  res.json({ url: fileUrl, filename: req.file.filename })
})

app.post('/api/recipes', requireAuth, (req, res) => {
  const recipes = readRecipes()
  const newRecipe = req.body
  
  if (!newRecipe.title || !newRecipe.description) {
    return res.status(400).json({ error: 'Titel und Beschreibung sind erforderlich' })
  }
  
  const maxId = recipes.length > 0 ? Math.max(...recipes.map(r => r.id)) : 0
  newRecipe.id = maxId + 1
  
  recipes.push(newRecipe)
  
  if (writeRecipes(recipes)) {
    res.status(201).json(newRecipe)
  } else {
    res.status(500).json({ error: 'Fehler beim Speichern des Rezepts' })
  }
})

app.put('/api/recipes/:id', requireAuth, (req, res) => {
  const recipes = readRecipes()
  const id = parseInt(req.params.id)
  const index = recipes.findIndex(r => r.id === id)
  
  if (index === -1) {
    return res.status(404).json({ error: 'Rezept nicht gefunden' })
  }
  
  const updatedRecipe = { ...recipes[index], ...req.body, id }
  recipes[index] = updatedRecipe
  
  if (writeRecipes(recipes)) {
    res.json(updatedRecipe)
  } else {
    res.status(500).json({ error: 'Fehler beim Aktualisieren des Rezepts' })
  }
})

app.delete('/api/recipes/:id', requireAuth, (req, res) => {
  const recipes = readRecipes()
  const id = parseInt(req.params.id)
  const index = recipes.findIndex(r => r.id === id)
  
  if (index === -1) {
    return res.status(404).json({ error: 'Rezept nicht gefunden' })
  }
  
  recipes.splice(index, 1)
  
  if (writeRecipes(recipes)) {
    res.status(204).send()
  } else {
    res.status(500).json({ error: 'Fehler beim Löschen des Rezepts' })
  }
})

app.listen(PORT, () => {
  console.log(`Server läuft auf http://localhost:${PORT}`)
})
