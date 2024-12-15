document.addEventListener('DOMContentLoaded', function() {
    const settingsForm = document.querySelector('.form');
    
    // Optional: Add client-side validation
    settingsForm.addEventListener('submit', function(e) {
        const hospitalName = document.getElementById('hospital_name');
        const contactEmail = document.getElementById('contact_email');
        const contactPhone = document.getElementById('contact_phone');
        
        // Basic validation
        if (hospitalName.value.trim() === '') {
            e.preventDefault();
            alert('Hospital name cannot be empty');
            hospitalName.focus();
            return;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(contactEmail.value)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            contactEmail.focus();
            return;
        }
        
        // Phone validation (basic)
        const phoneRegex = /^[0-9\-\(\)\s]+$/;
        if (!phoneRegex.test(contactPhone.value)) {
            e.preventDefault();
            alert('Please enter a valid phone number');
            contactPhone.focus();
            return;
        }
    });
});
