// Exemples d'utilisation des API avec JavaScript/Fetch

const API_URL = 'http://localhost:8000/api';

// ===== UTILISATEURS =====

// Créer un utilisateur
async function createUser(userData) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer tous les utilisateurs
async function getAllUsers() {
    try {
        const response = await fetch(`${API_URL}/utilisateurs`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer un utilisateur par ID
async function getUserById(id) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`);
        if (!response.ok) throw new Error('Utilisateur non trouvé');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Mettre à jour un utilisateur
async function updateUser(id, userData) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Supprimer un utilisateur
async function deleteUser(id) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`, {
            method: 'DELETE'
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// ===== MENUS =====

// Récupérer tous les menus
async function getAllMenus() {
    try {
        const response = await fetch(`${API_URL}/menus`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Filtrer les menus par régime
async function getMenusByRegime(regime) {
    try {
        const response = await fetch(`${API_URL}/menus?regime=${regime}`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Créer un menu
async function createMenu(menuData) {
    try {
        const response = await fetch(`${API_URL}/menus`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(menuData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Mettre à jour un menu
async function updateMenu(id, menuData) {
    try {
        const response = await fetch(`${API_URL}/menus/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(menuData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// ===== COMMANDES =====

// Créer une commande
async function createCommande(commandeData) {
    try {
        const response = await fetch(`${API_URL}/commandes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commandeData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer toutes les commandes
async function getAllCommandes() {
    try {
        const response = await fetch(`${API_URL}/commandes`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer une commande par ID
async function getCommandeById(id) {
    try {
        const response = await fetch(`${API_URL}/commandes/${id}`);
        if (!response.ok) throw new Error('Commande non trouvée');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Mettre à jour une commande
async function updateCommande(id, commandeData) {
    try {
        const response = await fetch(`${API_URL}/commandes/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commandeData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// ===== AVIS =====

// Créer un avis
async function createAvis(aviData) {
    try {
        const response = await fetch(`${API_URL}/avis`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(aviData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer tous les avis
async function getAllAvis() {
    try {
        const response = await fetch(`${API_URL}/avis`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// ===== CONTACT =====

// Envoyer un message de contact
async function sendContact(contactData) {
    try {
        const response = await fetch(`${API_URL}/contact`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(contactData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// Récupérer tous les messages de contact
async function getAllMessages() {
    try {
        const response = await fetch(`${API_URL}/contact`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
    }
}

// ===== EXEMPLES D'UTILISATION =====

// Exemple: Créer un nouvel utilisateur
async function exampleCreateUser() {
    const result = await createUser({
        email: 'jean.dupont@example.com',
        password: 'SecurePassword123',
        prenom: 'Jean',
        telephone: '0123456789',
        ville: 'Paris',
        pays: 'France',
        adresse_postale: '123 Rue de Paris',
        role_id: 2 // client
    });
    console.log('Nouvel utilisateur:', result);
}

// Exemple: Afficher tous les menus Vegan
async function exampleGetVeganMenus() {
    const menus = await getMenusByRegime('Vegan');
    console.log('Menus Vegan:', menus);
}

// Exemple: Créer une commande
async function exampleCreateCommande() {
    const result = await createCommande({
        utilisateur_id: 1,
        date_commandante: '2026-02-20',
        date_livraison: '2026-02-25',
        heure_livraison: '18:00',
        prix_menu: 142.00,
        nombre_personnes: 4,
        prix_livraison: 15.00,
        pret_materiel: true,
        etat_materiel: 'neuf',
        statut: 'confirmée'
    });
    console.log('Commande créée:', result);
}

// Exemple: Envoyer un formulaire de contact
async function exampleSendContact() {
    const result = await sendContact({
        email: 'client@example.com',
        titre: 'Question sur les services',
        message: 'Proposez-vous un service de...'
    });
    console.log('Message envoyé:', result);
}

// Exemple: Laisser un avis
async function exampleCreateAvis() {
    const result = await createAvis({
        utilisateur_id: 1,
        titre: 'Excellent service!',
        description: 'Service magnifique, nourriture délicieuse',
        note: 5,
        statut: 'en attente'
    });
    console.log('Avis créé:', result);
}

// Export pour utilisation en modules ES6
export {
    createUser, getAllUsers, getUserById, updateUser, deleteUser,
    getAllMenus, getMenusByRegime, createMenu, updateMenu,
    createCommande, getAllCommandes, getCommandeById, updateCommande,
    createAvis, getAllAvis,
    sendContact, getAllMessages
};
