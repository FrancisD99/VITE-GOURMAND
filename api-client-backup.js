// Client API minimaliste pour test
const API_URL = '/api';

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
        console.error('Erreur login:', error);
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
        console.error('Erreur logout:', error);
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
