import { sendContact, createUser } from './api-client.js';
import { validators } from './validators.js';

// Hamburger Menu
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close menu when a link is clicked
const navLinks = navMenu.querySelectorAll('a');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    });
});

// Menu Filter
const filterBtns = document.querySelectorAll('.filter-btn');
const menuItems = document.querySelectorAll('.menu-item');

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all buttons
        filterBtns.forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        btn.classList.add('active');

        // Get the filter value
        const filter = btn.getAttribute('data-filter');

        // Filter menu items
        menuItems.forEach(item => {
            if (filter === 'all') {
                item.classList.remove('hidden');
            } else {
                const categories = item.getAttribute('data-category').split(' ');
                if (categories.includes(filter)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            }
        });
    });
});

// Form handling
const contactForm = document.querySelector('.contact-form form');
const loginForm = document.querySelector('.login-form');
const signupForm = document.querySelector('.signup-form');

if (contactForm) {
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = contactForm.querySelector('input[type="email"]').value;
        const titre = contactForm.querySelector('input[type="text"]').value;
        const message = contactForm.querySelector('textarea').value;
        
        try {
            const response = await sendContact({
                email,
                titre,
                message
            });
            
            if (response.message) {
                alert('✓ Merci pour votre message! Nous vous recontacterons bientôt.');
                contactForm.reset();
            } else if (response.error) {
                alert('✗ Erreur: ' + response.error);
            }
        } catch (error) {
            alert('✗ Erreur lors de l\'envoi: ' + error.message);
        }
    });
}

if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = loginForm.querySelector('input[type="email"]').value;
        
        // TODO: Implémenter l'authentification avec l'API
        alert(`Bienvenue! Vous êtes connecté avec: ${email}`);
        loginForm.reset();
    });
}

if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const nom = document.getElementById('signup-nom')?.value || '';
        const prenom = document.getElementById('signup-prenom')?.value || '';
        const email = document.getElementById('signup-email')?.value || '';
        const password = document.getElementById('signup-password')?.value || '';
        const passwordConfirm = document.getElementById('signup-password-confirm')?.value || '';
        const telephone = document.getElementById('signup-telephone')?.value || '';
        const adresse = document.getElementById('signup-adresse')?.value || '';

        // Validation des champs obligatoires
        if (!nom || !prenom || !email || !password) {
            alert('✗ Veuillez remplir tous les champs requis!');
            return;
        }

        // Validation de la correspondance des mots de passe
        if (password !== passwordConfirm) {
            alert('✗ Les mots de passe ne correspondent pas!');
            return;
        }

        // Validation stricte du mot de passe
        const passwordValidation = validators.validatePassword(password);
        if (!passwordValidation.isValid) {
            const errorsList = passwordValidation.errors.map(err => '• ' + err).join('\n');
            alert(`✗ Mot de passe non sécurisé:\n\n${errorsList}\n\n${validators.getPasswordRequirements()}`);
            return;
        }

        try {
            const response = await createUser({
                email,
                password,
                nom,
                prenom,
                telephone: telephone || null,
                ville: '',
                pays: 'France',
                adresse_postale: adresse || ''
            });
            
            if (response.message) {
                alert(`✓ Inscription réussie! Vérifiez votre email pour confirmer votre compte.`);
                signupForm.reset();
            } else if (response.error) {
                alert('✗ Erreur: ' + response.error);
            }
        } catch (error) {
            alert('✗ Erreur lors de l\'inscription: ' + error.message);
        }
    });
}

// Smooth scroll on page load
window.addEventListener('load', () => {
    // Add animation class to elements
    const elements = document.querySelectorAll('.about-content h2, .testimonial-card, .menu-item');
    elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.animation = `fadeInUp 0.6s ease forwards`;
        el.style.animationDelay = `${index * 0.1}s`;
    });
});

// Add animation keyframes dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Lazy loading for images
document.addEventListener('DOMContentLoaded', () => {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.loading = 'lazy';
    });
});
