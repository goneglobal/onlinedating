// JavaScript for Online Dating Application

// Tab functionality for login/register
function showTab(tabName) {
    // Hide all forms
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => form.classList.remove('active'));
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Show selected form and activate tab
    document.getElementById(tabName + '-form').classList.add('active');
    event.target.classList.add('active');
}

// Handle like/reject actions
function handleAction(userId, action) {
    const card = document.querySelector(`[data-user-id="${userId}"]`);
    
    // Add loading state
    const buttons = card.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Send AJAX request
    fetch('actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${action}&user_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show feedback
            showNotification(data.message, data.match ? 'success' : 'info');
            
            // Animate card removal
            card.style.transform = action === 'like' ? 'translateX(100%)' : 'translateX(-100%)';
            card.style.opacity = '0';
            
            setTimeout(() => {
                card.remove();
                
                // Check if no more cards
                const remainingCards = document.querySelectorAll('.profile-card').length;
                if (remainingCards === 0) {
                    showNoMoreCards();
                }
            }, 300);
            
            // If it's a match, show special animation
            if (data.match) {
                showMatchAnimation();
            }
        } else {
            showNotification(data.message, 'error');
            // Re-enable buttons
            buttons.forEach(btn => btn.disabled = false);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong. Please try again.', 'error');
        // Re-enable buttons
        buttons.forEach(btn => btn.disabled = false);
    });
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 2rem;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    
    // Set background color based on type
    switch (type) {
        case 'success':
            notification.style.background = 'linear-gradient(45deg, #4caf50, #66bb6a)';
            break;
        case 'error':
            notification.style.background = 'linear-gradient(45deg, #f44336, #ef5350)';
            break;
        default:
            notification.style.background = 'linear-gradient(45deg, #2196f3, #42a5f5)';
    }
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Show match animation
function showMatchAnimation() {
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(233, 30, 99, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2000;
        animation: fadeIn 0.5s ease;
    `;
    
    overlay.innerHTML = `
        <div style="text-align: center; color: white;">
            <div style="font-size: 5rem; animation: bounce 1s ease infinite;">💕</div>
            <h2 style="font-size: 3rem; margin: 1rem 0;">It's a Match!</h2>
            <p style="font-size: 1.5rem;">You both liked each other</p>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Remove after 3 seconds
    setTimeout(() => {
        overlay.style.animation = 'fadeOut 0.5s ease';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 500);
    }, 3000);
}

// Show no more cards message
function showNoMoreCards() {
    const container = document.querySelector('.cards-container');
    if (container) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #666;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">😔</div>
                <h3>No more profiles to show</h3>
                <p>Check back later for new people!</p>
                <a href="matches.php" class="btn-primary" style="margin-top: 1rem;">View Your Matches</a>
            </div>
        `;
    }
}

// Form validation
function validateRegistrationForm() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return false;
    }
    
    if (password.length < 6) {
        showNotification('Password must be at least 6 characters long', 'error');
        return false;
    }
    
    return true;
}

// Auto-resize textarea
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation to registration form
    const registerForm = document.querySelector('#register-form form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegistrationForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Auto-resize textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            autoResizeTextarea(this);
        });
    });
    
    // Message form auto-submit on Ctrl+Enter
    const messageTextarea = document.querySelector('.message-form textarea');
    if (messageTextarea) {
        messageTextarea.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    }
    
    // Smooth scroll to new messages
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }
    
    // Add loading states to buttons
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Loading...';
            }
        });
    });
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% {
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            transform: translate3d(0, -30px, 0);
        }
        70% {
            transform: translate3d(0, -15px, 0);
        }
        90% {
            transform: translate3d(0, -4px, 0);
        }
    }
    
    .profile-card {
        transition: transform 0.3s ease, opacity 0.3s ease;
    }
    
    .message {
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);