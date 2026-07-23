document.addEventListener('DOMContentLoaded', function() {
    // Initialize form elements
    const form = document.getElementById('reservationForm');
    const institutionSelect = document.getElementById('institution');
    const serviceSelect = document.getElementById('service');
    const dateInput = document.getElementById('date');
    const timeSelect = document.getElementById('time');
    const phoneInput = document.getElementById('phone');

    // Set minimum date to today and initialize value
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    dateInput.value = today;

    // Phone number formatting
    phoneInput.addEventListener('input', function(e) {
        // Remove all non-digit characters
        let phoneNumber = this.value.replace(/\D/g, '');
        
        // Format as +XX X XX XXX XXX
        if (phoneNumber.length > 0) {
            phoneNumber = '+' + phoneNumber;
            if (phoneNumber.length > 3) {
                phoneNumber = phoneNumber.substring(0, 3) + ' ' + phoneNumber.substring(3);
            }
            if (phoneNumber.length > 6) {
                phoneNumber = phoneNumber.substring(0, 6) + ' ' + phoneNumber.substring(6);
            }
            if (phoneNumber.length > 9) {
                phoneNumber = phoneNumber.substring(0, 9) + ' ' + phoneNumber.substring(9);
            }
            if (phoneNumber.length > 13) {
                phoneNumber = phoneNumber.substring(0, 13);
            }
        }
        
        this.value = phoneNumber;
    });

    // Load services when institution changes
    institutionSelect.addEventListener('change', function() {
        const institutionId = this.value;
        loadServices(institutionId);
    });

    // Initialize services for default institution
    if (institutionSelect.value) {
        loadServices(institutionSelect.value);
    }

    // Update times when date changes
    dateInput.addEventListener('change', function() {
        updateAvailableTimes(this.value);
    });

    // Initialize times for today
    updateAvailableTimes(today);

    // Form submission handler
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('.btn');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Envoi en cours...';

        try {
            // Validate form
            if (!validateForm()) {
                throw new Error('Veuillez remplir tous les champs obligatoires');
            }

            // Create FormData object from the form
            const formData = new FormData(form);
            
            // Log form data for debugging
            console.log('Submitting form data:', Object.fromEntries(formData.entries()));

            const response = await fetch('reservation.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('Server response:', result);

            if (result.success) {
                // Redirect to queue page with ticket number
                window.location.href = result.redirect_url;
            } else {
                throw new Error(result.error || 'Échec de la réservation');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Erreur: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
        }
    });

    // Function to load services based on institution
    async function loadServices(institutionId) {
        if (!institutionId) {
            serviceSelect.innerHTML = '<option value="">Sélectionnez d\'abord une institution</option>';
            return;
        }

        // Show loading state
        serviceSelect.innerHTML = '<option value="">Chargement des services...</option>';
        serviceSelect.disabled = true;

        try {
            const response = await fetch(`get_services.php?institution_id=${institutionId}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const services = await response.json();

            // Populate services dropdown
            serviceSelect.innerHTML = '<option value="">Sélectionnez un service</option>';
            
            if (services.length > 0) {
                services.forEach(service => {
                    const option = new Option(service.name, service.id);
                    serviceSelect.add(option);
                });
            } else {
                serviceSelect.innerHTML = '<option value="">Aucun service disponible</option>';
            }
        } catch (error) {
            console.error('Error loading services:', error);
            serviceSelect.innerHTML = '<option value="">Erreur de chargement des services</option>';
        } finally {
            serviceSelect.disabled = false;
        }
    }

    // Function to update available time slots
    function updateAvailableTimes(date) {
        // Clear existing options except the first one
        timeSelect.innerHTML = '<option value="">Choisissez un créneau</option>';
        timeSelect.disabled = true;

        // Parse selected date
        const selectedDate = new Date(date);
        const day = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Determine opening hours
        let startHour, endHour;
        if (day === 0) { // Sunday
            addDisabledOption(timeSelect, "Fermé le dimanche");
            return;
        } else if (day === 6) { // Saturday
            startHour = 10;
            endHour = 12;
        } else { // Weekday
            startHour = 9;
            endHour = 17;
        }

        // For today's date, don't show past times
        const isToday = selectedDate.getTime() === today.getTime();
        const currentHour = new Date().getHours();
        const currentMinute = new Date().getMinutes();

        // Add time slots every 30 minutes
        for (let hour = startHour; hour < endHour; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                // Skip past times for today
                if (isToday) {
                    if (hour < currentHour || (hour === currentHour && minute < currentMinute)) {
                        continue;
                    }
                }

                const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                const option = new Option(timeString, timeString);
                timeSelect.add(option);
            }
        }

        timeSelect.disabled = false;
    }

    // Helper function to add disabled option
    function addDisabledOption(selectElement, text) {
        const option = new Option(text, "");
        option.disabled = true;
        selectElement.add(option);
    }

    // Form validation function
    function validateForm() {
        let isValid = true;
        
        // Check all required fields
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('error');
                isValid = false;
            } else {
                field.classList.remove('error');
            }
        });

        // Specific validations
        if (phoneInput.value.replace(/\D/g, '').length < 8) {
            phoneInput.classList.add('error');
            isValid = false;
        }

        return isValid;
    }
});