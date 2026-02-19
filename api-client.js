// Client API pour Vite & Gourmand
// Utilisation: import { createUser, sendContact } from './api-client.js'

const API_URL = 'http://localhost:8000/api';

// ===== UTILISATEURS =====

export async function createUser(userData) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getAllUsers() {
    try {
        const response = await fetch(`${API_URL}/utilisateurs`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getUserById(id) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`);
        if (!response.ok) throw new Error('Utilisateur non trouvé');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function updateUser(id, userData) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function deleteUser(id) {
    try {
        const response = await fetch(`${API_URL}/utilisateurs/${id}`, {
            method: 'DELETE'
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

// ===== MENUS =====

export async function getAllMenus() {
    try {
        const response = await fetch(`${API_URL}/menus`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getMenusByRegime(regime) {
    try {
        const response = await fetch(`${API_URL}/menus?regime=${regime}`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function createMenu(menuData) {
    try {
        const response = await fetch(`${API_URL}/menus`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(menuData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function updateMenu(id, menuData) {
    try {
        const response = await fetch(`${API_URL}/menus/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(menuData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

// ===== COMMANDES =====

export async function createCommande(commandeData) {
    try {
        const response = await fetch(`${API_URL}/commandes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commandeData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getAllCommandes() {
    try {
        const response = await fetch(`${API_URL}/commandes`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getCommandeById(id) {
    try {
        const response = await fetch(`${API_URL}/commandes/${id}`);
        if (!response.ok) throw new Error('Commande non trouvée');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function updateCommande(id, commandeData) {
    try {
        const response = await fetch(`${API_URL}/commandes/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commandeData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

// ===== AVIS =====

export async function createAvis(aviData) {
    try {
        const response = await fetch(`${API_URL}/avis`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(aviData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getAllAvis() {
    try {
        const response = await fetch(`${API_URL}/avis`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

// ===== CONTACT =====

export async function sendContact(contactData) {
    try {
        const response = await fetch(`${API_URL}/contact`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(contactData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getAllMessages() {
    try {
        const response = await fetch(`${API_URL}/contact`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}
