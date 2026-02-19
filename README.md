# Vite & Gourmand - Restaurant Website

Un site web responsive moderne pour le restaurant "Vite & Gourmand" construit avec Vite, HTML, CSS et JavaScript.

## Caractéristiques

✨ **Responsive Design** - Fonctionne parfaitement sur tous les appareils (mobile, tablette, desktop)
🎨 **Design Moderne** - Interface élégante avec palette de couleurs jaune/noir
📱 **Mobile First** - Navigation adaptée avec menu hamburger pour mobile
🍽️ **Sections Complètes**:
  - Hero/Accueil
  - À propos de nous
  - Menu avec filtres
  - Témoignages clients
  - Formulaire de contact
  - Connexion/Inscription
  - Footer

## Installation

### Prérequis
- Node.js (v14 ou supérieur)
- npm ou yarn

### Étapes

1. **Naviguer vers le dossier du projet**
```bash
cd "Vite et gourmand"
```

2. **Installer les dépendances**
```bash
npm install
```

3. **Démarrer le serveur de développement**
```bash
npm run dev
```

Le site s'ouvrira automatiquement à http://localhost:3000

## Scripts

```bash
# Démarrer le serveur de développement
npm run dev

# Construire pour la production
npm run build

# Prévisualiser la version build
npm run preview
```

## Structure du Projet

```
Vite et gourmand/
├── index.html          # Fichier HTML principal
├── style.css           # Styles CSS complets
├── main.js             # JavaScript interactif
├── vite.config.js      # Configuration Vite
├── package.json        # Dépendances du projet
└── README.md           # Documentation
```

## Sections du Site

### 1. Navigation
- Menu responsif avec hamburger pour mobile
- Logo et liens vers les sections principales

### 2. Hero Section
- Image de présentation
- Titre principal "Vite & Gourmand"
- Bouton d'appel à l'action

### 3. À Propos
- Présentation du restaurant
- 3 points clés (Équipe, Produits, Fait maison)

### 4. Menu
- Filtrage par type de régime (Tous, Classique, Vegan, Végétarien)
- Grid responsive des plats
- Liens vers détails

### 5. Témoignages
- Cards clients avec citations
- Layout responsive

### 6. Contact
- Formulaire de contact
- Carte d'adresse (placeholder)

### 7. Authentification
- Formulaire de connexion
- Formulaire d'inscription

### 8. Footer
- Infos de contact
- Horaires
- Liens utiles

## Customisation

### Couleurs
Les couleurs peuvent être modifiées dans les variables CSS du fichier `style.css`:
```css
:root {
    --primary-yellow: #FCD34D;
    --secondary-yellow: #F59E0B;
    --dark-bg: #1F2937;
    --white: #FFFFFF;
    --black: #000000;
}
```

### Images
Les images sont actuellement des placeholders avec dégradés. Pour ajouter de vraies images:
1. Créer un dossier `images/` dans le projet
2. Remplacer les divs `placeholder-image` par des balises `<img>`
3. Mettre à jour les chemins d'accès dans le CSS/HTML

### Contenu
Tous les textes peuvent être édités directement dans `index.html`

## Responsive Breakpoints

- **Desktop**: > 768px
- **Tablette**: 481px - 768px
- **Mobile**: < 480px

## Fonctionnalités JavaScript

1. **Menu Hamburger** - Toggle du menu sur mobile
2. **Filtrage du Menu** - Filtrer les plats par catégorie
3. **Gestion des Formulaires** - Validation et messages de confirmation
4. **Animations** - Fade-in au chargement
5. **Smooth Scroll** - Navigation fluide vers les sections

## Déploiement

Pour déployer le site sur une plateforme (Vercel, Netlify, GitHub Pages):

```bash
npm run build
```

Le dossier `dist/` contendra les fichiers prêts pour la production.

## Support

Pour toute question ou problème, veuillez contacter l'équipe de développement.

---

**Année**: 2026
**Restaurant**: Vite & Gourmand
**Adresse**: 123 Anywhere St., Any City, State, Country 12345
