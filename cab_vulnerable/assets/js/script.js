// QuickCab - Client-side JavaScript

// Form validation helper
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Calculate fare based on distance (simple estimation)
function calculateFare(pickupLat, pickupLng, dropoffLat, dropoffLng) {
    // Simple distance calculation (not accurate, just for demo)
    const distance = Math.abs(dropoffLat - pickupLat) + Math.abs(dropoffLng - pickupLng);
    const baseFare = 10;
    const perUnitRate = 5;
    return baseFare + (distance * perUnitRate * 100);
}

// Star rating display
function displayStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars;
}

// Confirmation dialogs
function confirmAction(message) {
    return confirm(message);
}

// Real-time fare estimation on booking page
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Driver selection highlight
    const driverCards = document.querySelectorAll('.driver-card');
    driverCards.forEach(card => {
        card.addEventListener('click', function() {
            driverCards.forEach(c => c.classList.remove('border-warning'));
            this.classList.add('border-warning', 'border-3');
        });
    });
});

// Profile picture preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
