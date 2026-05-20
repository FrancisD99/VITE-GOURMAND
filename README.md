Vite & Gourmand - Site Internet du Restaurant
Un site web responsive moderne pour le restaurant "Vite & Gourmand" construit avec Vite, HTML, CSS et JavaScript.

Caractéristiques
✨ Responsive Design - Fonctionne parfaitement sur tous les appareils (mobile, tablette, ordinateur de bureau) 🎨 Design Moderne - Interface élégante avec palette de couleurs jaune/noir 📱 Mobile First - Navigation adaptée avec menu hamburger pour mobile 🍽️ Sections Complètes :

Héros/Accueil
À propos de nous
Menu avec filtres
Témoignages clients
Formulaire de contact
Connexion/Inscription
Pied de page
Installation
Prérequis
Node.js (v14 ou supérieur)
npm ou yarn
Étapes
Naviguer vers le dossier du projet
cd "Vite et gourmand"
Installer les dépendances
npm install
Démarrer le serveur de développement
npm run dev
Le site s'ouvrira automatiquement à http://localhost:3000

Scénarios
# Démarrer le serveur de développement
npm run dev

# Construire pour la production
npm run build

# Prévisualiser la version build
npm run preview
Structure du Projet
Vite et gourmand/
├── index.html          # Fichier HTML principal
├── style.css           # Styles CSS complets
├── main.js             # JavaScript interactif
├── vite.config.js      # Configuration Vite
├── package.json        # Dépendances du projet
└── README.md           # Documentation
Sections du Site
1. Navigation
Menu responsif avec hamburger pour mobile
Logo et liens vers les sections principales
2. Section Héros
Image de présentation
Titre principal "Vite & Gourmand"
Bouton d'appel à l'action
3. À propos
Présentation du restaurant
3 points clés (Équipe, Produits, Fait maison)
4. Menu
Filtrage par type de régime (Tous, Classique, Vegan, Végétarien)
Grid responsive des plats
Liens vers détails
5. Témoignages
Cartes clients avec citations
Mise en page adaptative
6. Contact
Formulaire de contact
Carte d'adresse (placeholder)
7. Authentification
Formulaire de connexion
Formulaire d'inscription
8. Pied de page
Informations de contact
Horaires
Liens utiles
🔧 API backend
Le projet inclut un backend complet en PHP avec sécurité et conformité :

Architecture
backend/
├── api/index.php              # API REST
├── config/
│   ├── Database.php           # Connexion MySQL
│   ├── JWTHandler.php         # Authentification JWT
│   ├── AuthHelper.php         # Vérification permission
│   ├── PermissionManager.php  # RBAC avancé
│   └── AuditLogger.php        # Journalisation d'audit
├── models/                    # Modèles CRUD
└── etape5.1-audit.sql         # Schéma audit complet
Étapes d'implémentation
Étape	Fonctionnalité	Documentation	Test
Base	Modèles CRUD	backend/README.md	test.html
3	Authentification JWT	ETAPE3-JWT-GUIDE.md	test-etape3.html
4	Points de terminaison sécurisés	ETAPE4-PROTECTION-GUIDE.md	test-etape4.html
5	Rôles et permissions	ETAPE5-RBAC-GUIDE.md	test-etape5.html
5.1	Journalisation des audits 🆕	ETAPE5.1-AUDIT-GUIDE.md	test-etape5.1.html
Étape 5.1 - Journalisation des audits
Système complet de journalisation d'audit pour :

✅ Conformité - RGPD, HIPAA, PCI-DSS
✅ Sécurité - Tracer toutes les opérations sensibles
✅ Enquête - Rechercher qui a fait quoi et quand
✅ Analytics - Voir les tendances d'accès
Caractéristiques :

Journalisation automatique des CRUD (créer, mettre à jour, supprimer)
Filtrage & recherche avancée par utilisateur, action, ressource, période
Statistiques en temps réel (succès/refusés)
Exporter le fichier CSV pour conformité
Rétention configurable (90-730 jours par ressource)
Nettoyage automatique des bûches expirées
Client IP de détection (compatible proxy/Cloudflare)
Lancer les tests :

php -S localhost:8000(dans backend/)
Importer backend/etape5.1-audit.sqldans MySQL
Ouvrir test-etape5.1.htmldans le navigateur
Pour l'installation détaillée : voirETAPE5.1-INSTALLATION.md

Personnalisation
Couleurs
Les couleurs peuvent être modifiées dans les variables CSS du fichier style.css:

:root {
    --primary-yellow: #FCD34D;
    --secondary-yellow: #F59E0B;
    --dark-bg: #1F2937;
    --white: #FFFFFF;
    --black: #000000;
}
Images
Les images sont actuellement des placeholders avec dégradés. Pour ajouter des images vraies :

Créer un dossier images/dans le projet
Remplacer les divs placeholder-imagepar des balises<img>
Mettre à jour les chemins d'accès dans le CSS/HTML
Contenu
Tous les textes peuvent être édités directement dansindex.html

Points de rupture réactifs
Ordinateur de bureau : > 768px
Tablette : 481px - 768px
Mobile : < 480px
Fonctionnalités JavaScript
Menu Hamburger - Basculer le menu sur mobile
Filtrage du Menu - Filtrer les plats par catégorie
Gestion des Formulaires - Validation et messages de confirmation
Animations - Fondu au chargement
Smooth Scroll - Navigation fluide vers les sections
Déploiement
Pour déployer le site sur une plateforme (Vercel, Netlify, GitHub Pages) :

npm run build
Le dossier dist/contient les fichiers prêts pour la production.

Soutien
Pour toute question ou problème, veuillez contacter l'équipe de développement.

Année : 2026 Restaurant : Vite & Gourmand Adresse : 123 Anywhere St., Any City, State, Country 12345
