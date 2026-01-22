/**
 * ========================================
 * MAIN.JS - Département des Sciences de la Vie (SVI)
 * JavaScript principal pour les fonctionnalités du site
 * ========================================
 */

// ========================================
// NAVIGATION MOBILE
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            
            // Animation du bouton hamburger
            const spans = navToggle.querySelectorAll('span');
            spans.forEach((span, index) => {
                span.style.transform = navMenu.classList.contains('active') 
                    ? (index === 0 ? 'rotate(45deg) translate(5px, 5px)' 
                       : index === 1 ? 'scale(0)' 
                       : 'rotate(-45deg) translate(7px, -6px)')
                    : 'none';
            });
        });
        
        // Fermer le menu en cliquant sur un lien
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const spans = navToggle.querySelectorAll('span');
                spans.forEach(span => span.style.transform = 'none');
            });
        });
    }
});

// ========================================
// SCROLL SMOOTH & HEADER BACKGROUND
// ========================================

window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    
    if (header) {
        if (window.scrollY > 50) {
            header.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        } else {
            header.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
        }
    }
});

// ========================================
// ANIMATION À L'APPARITION (INTERSECTION OBSERVER)
// ========================================

const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observer tous les éléments avec la classe slide-up
document.addEventListener('DOMContentLoaded', function() {
    const slideUpElements = document.querySelectorAll('.slide-up');
    slideUpElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
        observer.observe(el);
    });
});

// ========================================
// COMPTEUR ANIMÉ (STATS)
// ========================================

function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16); // 60 FPS
    
    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start);
        }
    }, 16);
}

// Démarrer l'animation des compteurs quand ils sont visibles
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
            const target = parseInt(entry.target.dataset.target);
            animateCounter(entry.target, target);
            entry.target.classList.add('counted');
        }
    });
}, { threshold: 0.5 });

document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => statsObserver.observe(stat));
});

// ========================================
// BLOG - DONNÉES DES ARTICLES
// ========================================

const blogArticles = [
    {
        id: 1,
        title: "La thérapie génique : espoirs et défis",
        category: "genetique",
        excerpt: "Les avancées récentes en thérapie génique ouvrent de nouvelles perspectives pour le traitement de maladies génétiques rares. Découvrez les dernières innovations.",
        author: "Dr. Amina Benali",
        date: "15 janvier 2026",
        image: "genetique"
    },
    {
        id: 2,
        title: "Biodiversité marine : urgence de protection",
        category: "environnement",
        excerpt: "Les écosystèmes marins sont menacés par le changement climatique et la pollution. Un appel à l'action pour préserver notre patrimoine océanique.",
        author: "Prof. Karim El Amrani",
        date: "10 janvier 2026",
        image: "environnement"
    },
    {
        id: 3,
        title: "CRISPR-Cas9 : révolution en édition génétique",
        category: "recherche",
        excerpt: "La technologie CRISPR continue de transformer la recherche biomédicale. Exploration des applications actuelles et futures de cet outil révolutionnaire.",
        author: "Dr. Sarah Idrissi",
        date: "8 janvier 2026",
        image: "recherche"
    },
    {
        id: 4,
        title: "Microbiome intestinal et santé mentale",
        category: "biologie",
        excerpt: "Des recherches récentes révèlent des liens fascinants entre notre microbiome intestinal et notre santé mentale. L'axe intestin-cerveau expliqué.",
        author: "Dr. Youssef Tazi",
        date: "5 janvier 2026",
        image: "biologie"
    },
    {
        id: 5,
        title: "Photosynthèse artificielle : l'énergie du futur",
        category: "recherche",
        excerpt: "Les scientifiques développent des systèmes de photosynthèse artificielle pour produire de l'énergie propre inspirée des plantes.",
        author: "Prof. Fatima Zahra",
        date: "2 janvier 2026",
        image: "recherche"
    },
    {
        id: 6,
        title: "Résistance aux antibiotiques : un défi mondial",
        category: "biologie",
        excerpt: "La résistance bactérienne aux antibiotiques représente une menace croissante pour la santé publique. État des lieux et solutions envisagées.",
        author: "Dr. Mohamed Alaoui",
        date: "28 décembre 2025",
        image: "biologie"
    },
    {
        id: 7,
        title: "Conservation des espèces endémiques marocaines",
        category: "environnement",
        excerpt: "Focus sur les efforts de conservation des espèces endémiques du Maroc, de l'ibis chauve au macaque de Barbarie.",
        author: "Dr. Laila Bennani",
        date: "20 décembre 2025",
        image: "environnement"
    },
    {
        id: 8,
        title: "L'épigénétique : au-delà de l'ADN",
        category: "genetique",
        excerpt: "Comment l'environnement et le mode de vie influencent l'expression de nos gènes sans modifier la séquence d'ADN.",
        author: "Prof. Hassan Chakir",
        date: "15 décembre 2025",
        image: "genetique"
    },
    {
        id: 9,
        title: "Plastiques biodégradables : solution ou illusion ?",
        category: "environnement",
        excerpt: "Analyse critique des plastiques biodégradables et de leur réel impact environnemental dans la lutte contre la pollution plastique.",
        author: "Dr. Nadia Berrada",
        date: "10 décembre 2025",
        image: "environnement"
    }
];

// ========================================
// BLOG - CRÉATION DES SVG POUR LES IMAGES
// ========================================

function createBlogSVG(category) {
    const svgTemplates = {
        genetique: `
            <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#e8f4f8"/>
                <path d="M100 50 Q150 100 200 50 Q150 100 200 150 Q150 100 100 150 Q150 100 100 200 Q150 150 200 200" 
                      fill="none" stroke="#3498db" stroke-width="8"/>
                <circle cx="100" cy="50" r="12" fill="#3498db"/>
                <circle cx="200" cy="50" r="12" fill="#3498db"/>
                <circle cx="100" cy="150" r="12" fill="#3498db"/>
                <circle cx="200" cy="150" r="12" fill="#3498db"/>
                <circle cx="100" cy="200" r="12" fill="#e74c3c"/>
                <circle cx="200" cy="200" r="12" fill="#2ecc71"/>
                <text x="250" y="150" font-size="48" fill="#2c3e50" font-weight="bold">ADN</text>
            </svg>
        `,
        environnement: `
            <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#e8f8f0"/>
                <circle cx="200" cy="150" r="80" fill="#3498db" opacity="0.3"/>
                <ellipse cx="200" cy="180" rx="120" ry="60" fill="#27ae60" opacity="0.6"/>
                <path d="M120 150 Q100 120 120 90" fill="none" stroke="#2ecc71" stroke-width="3"/>
                <circle cx="120" cy="90" r="25" fill="#27ae60" opacity="0.5"/>
                <path d="M280 140 Q300 110 280 80" fill="none" stroke="#2ecc71" stroke-width="3"/>
                <circle cx="280" cy="80" r="30" fill="#27ae60" opacity="0.5"/>
                <path d="M200 100 Q180 70 200 40" fill="none" stroke="#16a085" stroke-width="4"/>
                <circle cx="200" cy="40" r="35" fill="#2ecc71" opacity="0.6"/>
            </svg>
        `,
        recherche: `
            <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#fef5e7"/>
                <circle cx="200" cy="120" r="70" fill="none" stroke="#e74c3c" stroke-width="4"/>
                <line x1="250" y1="170" x2="300" y2="220" stroke="#34495e" stroke-width="8"/>
                <circle cx="300" cy="220" r="25" fill="#34495e" opacity="0.3"/>
                <circle cx="180" cy="100" r="15" fill="#3498db" opacity="0.5"/>
                <circle cx="220" cy="110" r="12" fill="#2ecc71" opacity="0.5"/>
                <circle cx="200" cy="140" r="18" fill="#f39c12" opacity="0.5"/>
                <path d="M100 250 L120 230 L140 240 L160 220 L180 230 L200 210" 
                      fill="none" stroke="#e74c3c" stroke-width="3"/>
            </svg>
        `,
        biologie: `
            <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="300" fill="#f0f9ff"/>
                <circle cx="200" cy="150" r="60" fill="none" stroke="#2ecc71" stroke-width="4"/>
                <circle cx="200" cy="150" r="45" fill="none" stroke="#2ecc71" stroke-width="3"/>
                <circle cx="200" cy="150" r="30" fill="none" stroke="#27ae60" stroke-width="3"/>
                <circle cx="200" cy="150" r="15" fill="#2ecc71"/>
                <circle cx="120" cy="100" r="35" fill="#3498db" opacity="0.3"/>
                <circle cx="280" cy="100" r="30" fill="#e74c3c" opacity="0.3"/>
                <circle cx="150" cy="220" r="25" fill="#f39c12" opacity="0.3"/>
                <circle cx="250" cy="210" r="28" fill="#9b59b6" opacity="0.3"/>
            </svg>
        `
    };
    
    return svgTemplates[category] || svgTemplates.biologie;
}

// ========================================
// BLOG - AFFICHAGE DES ARTICLES
// ========================================

function displayBlogArticles(articles) {
    const blogGrid = document.getElementById('blogGrid');
    if (!blogGrid) return;
    
    blogGrid.innerHTML = '';
    
    articles.forEach(article => {
        const articleCard = document.createElement('div');
        articleCard.className = 'blog-card';
        articleCard.dataset.category = article.category;
        
        articleCard.innerHTML = `
            <div class="blog-image">
                ${createBlogSVG(article.category)}
            </div>
            <div class="blog-content">
                <span class="blog-category">${getCategoryName(article.category)}</span>
                <h3 class="blog-title">${article.title}</h3>
                <p class="blog-excerpt">${article.excerpt}</p>
                <div class="blog-meta">
                    <span class="blog-date">📅 ${article.date}</span>
                    <span class="blog-author">✍️ ${article.author}</span>
                </div>
            </div>
        `;
        
        blogGrid.appendChild(articleCard);
    });
}

function getCategoryName(category) {
    const names = {
        'biologie': 'Biologie',
        'genetique': 'Génétique',
        'environnement': 'Environnement',
        'recherche': 'Recherche'
    };
    return names[category] || category;
}

// ========================================
// BLOG - FILTRAGE PAR CATÉGORIE
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    
    if (filterBtns.length > 0) {
        // Afficher tous les articles au chargement
        displayBlogArticles(blogArticles);
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                filterBtns.forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                const category = this.dataset.category;
                
                if (category === 'all') {
                    displayBlogArticles(blogArticles);
                } else {
                    const filteredArticles = blogArticles.filter(
                        article => article.category === category
                    );
                    displayBlogArticles(filteredArticles);
                }
            });
        });
    }
});

// ========================================
// FORMULAIRE DE CONTACT - VALIDATION
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Réinitialiser les messages d'erreur
            const errorMessages = document.querySelectorAll('.error-message');
            errorMessages.forEach(msg => {
                msg.style.display = 'none';
                msg.textContent = '';
            });
            
            let isValid = true;
            
            // Validation du nom
            const name = document.getElementById('name');
            if (name.value.trim().length < 3) {
                showError('nameError', 'Le nom doit contenir au moins 3 caractères');
                isValid = false;
            }
            
            // Validation de l'email
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                showError('emailError', 'Veuillez entrer une adresse email valide');
                isValid = false;
            }
            
            // Validation du sujet
            const subject = document.getElementById('subject');
            if (subject.value === '') {
                showError('subjectError', 'Veuillez choisir un sujet');
                isValid = false;
            }
            
            // Validation du message
            const message = document.getElementById('message');
            if (message.value.trim().length < 10) {
                showError('messageError', 'Le message doit contenir au moins 10 caractères');
                isValid = false;
            }
            
            if (isValid) {
                // Simulation d'envoi
                const btnText = document.querySelector('.btn-text');
                const btnLoading = document.querySelector('.btn-loading');
                const formMessage = document.getElementById('formMessage');
                
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                
                setTimeout(() => {
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    
                    formMessage.className = 'form-message success';
                    formMessage.textContent = 'Merci ! Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.';
                    formMessage.style.display = 'block';
                    
                    contactForm.reset();
                    
                    setTimeout(() => {
                        formMessage.style.display = 'none';
                    }, 5000);
                }, 1500);
            }
        });
    }
});

function showError(elementId, message) {
    const errorElement = document.getElementById(elementId);
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// ========================================
// SCROLL TO TOP (optionnel)
// ========================================

// Créer un bouton "Retour en haut"
document.addEventListener('DOMContentLoaded', function() {
    // Créer le bouton
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.innerHTML = '↑';
    scrollTopBtn.className = 'scroll-top-btn';
    scrollTopBtn.setAttribute('aria-label', 'Retour en haut');
    
    // Styles inline pour le bouton
    scrollTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(scrollTopBtn);
    
    // Afficher/masquer le bouton selon le scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            scrollTopBtn.style.opacity = '1';
            scrollTopBtn.style.visibility = 'visible';
        } else {
            scrollTopBtn.style.opacity = '0';
            scrollTopBtn.style.visibility = 'hidden';
        }
    });
    
    // Action au clic
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Effet hover
    scrollTopBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 6px 16px rgba(0,0,0,0.3)';
    });
    
    scrollTopBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.2)';
    });
});

// ========================================
// GESTION DES ANCRES (SMOOTH SCROLL)
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Ignorer les liens vides ou juste "#"
            if (href === '#' || href === '') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                const headerOffset = 80;
                const elementPosition = target.offsetTop;
                const offsetPosition = elementPosition - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// ========================================
// MESSAGE DE BIENVENUE (Console)
// ========================================

console.log('%c🧬 Département des Sciences de la Vie (SVI)', 'color: #2ecc71; font-size: 20px; font-weight: bold;');
console.log('%cBienvenue sur notre site web !', 'color: #3498db; font-size: 14px;');
console.log('%c© 2026 - Tous droits réservés', 'color: #7f8c8d; font-size: 12px;');
