// Client API pour Vite & Gourmand
// Utilisation: import { createUser, sendContact } from './api-client.js'

const API_URL = 'http://localhost:8000/api';

// Stockage du token
let authToken = localStorage.getItem('auth_token');

export function setAuthToken(token) {
    authToken = token;
    localStorage.setItem('auth_token', token);
}

export function getAuthToken() {
    return authToken;
}

export function clearAuthToken() {
    authToken = null;
    localStorage.removeItem('auth_token');
}

function getAuthHeaders() {
    const headers = { 'Content-Type': 'application/json' };
    if (authToken) {
        headers['Authorization'] = `Bearer ${authToken}`;
    }
    return headers;
}

// ===== AUTHENTIFICATION =====

export async function login(email, password) {
    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });
        const result = await response.json();
        
        if (result.token) {
            setAuthToken(result.token);
        }
        
        return result;
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function logout() {
    try {
        const response = await fetch(`${API_URL}/auth/logout`, {
            method: 'POST',
            headers: getAuthHeaders()
        });
        
        clearAuthToken();
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getCurrentUser() {
    try {
        const response = await fetch(`${API_URL}/auth/me`, {
            method: 'GET',
            headers: getAuthHeaders()
        });
        
        if (!response.ok) {
            clearAuthToken();
            throw new Error('Unauthorized');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export function isAuthenticated() {
    return authToken !== null;
}

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

// ===== PLATS =====

export async function getAllPlats() {
    try {
        const response = await fetch(`${API_URL}/plats`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getPlatById(id) {
    try {
        const response = await fetch(`${API_URL}/plats/${id}`);
        if (!response.ok) throw new Error('Plat non trouvé');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getPlatsByRegime(regime_id) {
    try {
        const response = await fetch(`${API_URL}/plats?regime_id=${regime_id}`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function searchPlats(searchTerm) {
    try {
        const response = await fetch(`${API_URL}/plats?search=${encodeURIComponent(searchTerm)}`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function createPlat(platData) {
    try {
        const response = await fetch(`${API_URL}/plats`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(platData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function updatePlat(id, platData) {
    try {
        const response = await fetch(`${API_URL}/plats/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(platData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function deletePlat(id) {
    try {
        const response = await fetch(`${API_URL}/plats/${id}`, {
            method: 'DELETE'
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

// ===== ALLERGENES =====

export async function getAllAllergenes() {
    try {
        const response = await fetch(`${API_URL}/allergenes`);
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function getAllergeneById(id) {
    try {
        const response = await fetch(`${API_URL}/allergenes/${id}`);
        if (!response.ok) throw new Error('Allergène non trouvé');
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function createAllergene(allergeneData) {
    try {
        const response = await fetch(`${API_URL}/allergenes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(allergeneData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function updateAllergene(id, allergeneData) {
    try {
        const response = await fetch(`${API_URL}/allergenes/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(allergeneData)
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}

export async function deleteAllergene(id) {
    try {
        const response = await fetch(`${API_URL}/allergenes/${id}`, {
            method: 'DELETE'
        });
        return await response.json();
    } catch (error) {
        console.error('Erreur:', error);
        throw error;
    }
}
