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
    contactForm.addEventListener('submit', (e) => {
        e.preventDefault();
        alert('Merci pour votre message! Nous vous recontacterons bientôt.');
        contactForm.reset();
    });
}

if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const email = loginForm.querySelector('input[type="email"]').value;
        alert(`Bienvenue! Vous êtes connecté avec: ${email}`);
        loginForm.reset();
    });
}

if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = signupForm.querySelector('input[type="text"]').value;
        alert(`Merci de votre inscription, ${name}! Vérifié votre email pour confirmer votre compte.`);
        signupForm.reset();
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
