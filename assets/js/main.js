document.addEventListener('DOMContentLoaded', () => {
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // Add to cart animation (visual only for non-ajax)
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    addToCartBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Optional: you can add ajax add-to-cart here
            // Currently it submits a form or redirects
        });
    });

    // Premium Scroll Animations
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const animatedElements = document.querySelectorAll('.fade-up');
    animatedElements.forEach(el => observer.observe(el));
});

// Premium sharing utility for Instagram
function showToast(message) {
    let toast = document.getElementById('gloriolux-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'gloriolux-toast';
        document.body.appendChild(toast);
        
        const style = document.createElement('style');
        style.textContent = `
            #gloriolux-toast {
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%) translateY(100px);
                background: rgba(18, 18, 18, 0.95);
                backdrop-filter: blur(10px);
                color: #fff;
                padding: 14px 28px;
                border-radius: 50px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                font-family: 'Lato', sans-serif;
                font-size: 0.95rem;
                z-index: 10000;
                transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s;
                opacity: 0;
                display: flex;
                align-items: center;
                gap: 12px;
                pointer-events: none;
                border: 1px solid rgba(255,255,255,0.15);
            }
            #gloriolux-toast.show {
                transform: translateX(-50%) translateY(0);
                opacity: 1;
            }
            #gloriolux-toast i {
                color: #c13584;
                font-size: 1.2rem;
            }
        `;
        document.head.appendChild(style);
    }
    
    toast.innerHTML = `<i class="fab fa-instagram"></i> <span>${message}</span>`;
    toast.classList.add('show');
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 4500);
}

function copyTextToClipboard(text, callback) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(callback, (err) => {
            fallbackCopyTextToClipboard(text, callback);
        });
    } else {
        fallbackCopyTextToClipboard(text, callback);
    }
}

function fallbackCopyTextToClipboard(text, callback) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    try {
        const successful = document.execCommand('copy');
        if (successful && callback) callback();
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    document.body.removeChild(textArea);
}

window.shareToInstagram = function(event, title) {
    if (event) event.preventDefault();
    const url = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            text: 'Check out ' + title + ' from Gloriolux!',
            url: url
        }).then(() => {
            console.log('Shared successfully');
        }).catch((err) => {
            console.log('Error sharing:', err);
        });
    } else {
        copyTextToClipboard(url, () => {
            showToast('Link copied! Paste it in your Instagram Story sticker or DM.');
            setTimeout(() => {
                window.open('https://www.instagram.com', '_blank');
            }, 2000);
        });
    }
};
