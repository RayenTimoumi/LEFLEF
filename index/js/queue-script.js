document.addEventListener('DOMContentLoaded', function() {
    // Get ticket number from URL
    const urlParams = new URLSearchParams(window.location.search);
    const ticketNumber = urlParams.get('ticket');
    
    if (!ticketNumber) {
        redirectToReservationPage();
        return;
    }
    
    // Store ticket number in session storage for cancellation
    sessionStorage.setItem('current_ticket', ticketNumber);
    
    // Initialize UI elements
    const progressBar = document.getElementById('progress-bar');
    const notificationElement = document.getElementById('notification-text');
    const cancelButton = document.getElementById('cancel-button');
    
    // Set up cancellation handler
    if (cancelButton) {
        cancelButton.addEventListener('click', handleCancellation);
    }
    
    // Initial data fetch and setup
    initializeQueueMonitoring(ticketNumber);
    
    // Setup notification permissions
    setupNotifications();
});

// Main queue monitoring function
async function initializeQueueMonitoring(ticketNumber) {
    try {
        const reservation = await fetchReservationData(ticketNumber);
        
        if (!reservation) {
            showErrorAndRedirect('Réservation non trouvée');
            return;
        }
        
        // Display initial information
        displayReservationInfo(reservation);
        
        // Start real-time updates
        startQueueUpdates(ticketNumber, reservation.queue_position);
        
    } catch (error) {
        console.error('Initialization error:', error);
        showErrorAndRedirect('Erreur lors du chargement de la réservation');
    }
}

// Real-time queue updates
function startQueueUpdates(ticketNumber, initialPosition) {
    let lastPosition = initialPosition;
    let updateInterval;
    let smoothUpdateInterval;
    
    // Function to perform update
    const performUpdate = async () => {
        try {
            const updatedReservation = await fetchReservationData(ticketNumber);
            
            if (!updatedReservation) {
                clearInterval(updateInterval);
                clearInterval(smoothUpdateInterval);
                showErrorAndRedirect('Réservation annulée ou terminée');
                return;
            }
            
            const newPosition = updatedReservation.queue_position;
            
            // Update display
            displayReservationInfo(updatedReservation);
            
            // Show notification if position changed
            if (newPosition < lastPosition) {
                showNotification(`Votre position a changé: maintenant #${newPosition}`);
            }
            
            // Special notification when it's your turn
            if (newPosition === 1) {
                showNotification("C'est votre tour! Veuillez vous présenter au guichet.", true);
                clearInterval(updateInterval);
                clearInterval(smoothUpdateInterval);
            }
            
            lastPosition = newPosition;
        } catch (error) {
            console.error('Update error:', error);
        }
    };
    
    // Initial update and then every 30 seconds
    performUpdate();
    updateInterval = setInterval(performUpdate, 30000);
    
    // Smooth progress bar animation (60fps)
    smoothUpdateInterval = setInterval(() => {
        const currentPosition = parseInt(document.getElementById('queue-position').textContent) || lastPosition;
        updateProgressBarSmoothly(currentPosition);
    }, 16); // ~60fps
}

// Enhanced fetch function with timeout
async function fetchReservationData(ticketNumber) {
    try {
        // Add timeout to fetch (5 seconds)
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);
        
        const response = await fetch(`get_reservation.php?ticket=${ticketNumber}`, {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.reservation) {
            return data.reservation;
        } else {
            throw new Error(data.error || 'Reservation not found');
        }
    } catch (error) {
        console.error('Fetch error:', error);
        return null;
    }
}

// Enhanced display function
function displayReservationInfo(reservation) {
    if (!reservation) return;
    
    document.getElementById('ticket-number').textContent = reservation.ticket_number;
    document.getElementById('service-type').textContent = getServiceName(reservation.service);
    document.getElementById('queue-position').textContent = reservation.queue_position;
    
    // Calculate and display wait time
    const waitTime = calculateWaitTime(
        reservation.service, 
        reservation.reservation_time, 
        reservation.queue_position,
        reservation.estimated_wait
    );
    document.getElementById('wait-time').textContent = waitTime;
    
    // Immediate progress bar update
    updateProgressBar(reservation.queue_position);
}

// Smooth progress bar animation
let currentProgress = 0;
function updateProgressBarSmoothly(position) {
    const maxQueue = 20; // For visualization purposes
    const targetProgress = ((maxQueue - position) / maxQueue) * 100;
    
    // Smooth animation with easing
    currentProgress += (targetProgress - currentProgress) * 0.1;
    
    // Update progress bar
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = `${Math.min(100, Math.max(0, currentProgress))}%`;
    
    // Update color based on position
    if (position <= 3) {
        progressBar.style.backgroundColor = '#4CAF50'; // Green
    } else if (position <= 10) {
        progressBar.style.backgroundColor = '#FFC107'; // Yellow
    } else {
        progressBar.style.backgroundColor = '#F44336'; // Red
    }
}

// Immediate progress bar update
function updateProgressBar(position) {
    const maxQueue = 20;
    currentProgress = ((maxQueue - position) / maxQueue) * 100;
    document.getElementById('progress-bar').style.width = `${currentProgress}%`;
}

// Enhanced wait time calculation
function calculateWaitTime(service, time, position, estimatedWait) {
    if (estimatedWait !== undefined) {
        // Use server-provided estimate if available
        return formatWaitTime(estimatedWait);
    }
    
    // Fallback calculation
    const serviceTimes = {
        'etat-civil': 15,
        'urbanisme': 10,
        'social': 8,
        'technique': 5
    };
    
    const baseTime = serviceTimes[service] || 10;
    const calculatedTime = position * baseTime;
    
    return formatWaitTime(calculatedTime);
}

function formatWaitTime(minutes) {
    if (minutes >= 60) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return `${hours}h${mins < 10 ? '0' : ''}${mins}`;
    } else if (minutes <= 1) {
        return "Moins d'une minute";
    } else {
        return `${minutes} minutes`;
    }
}

// Enhanced notification system
function showNotification(message, isUrgent = false) {
    const notificationElement = document.getElementById('notification-text');
    
    // Update on-screen notification
    notificationElement.textContent = message;
    notificationElement.style.display = 'block';
    
    if (isUrgent) {
        notificationElement.style.backgroundColor = '#4CAF50';
        notificationElement.style.color = 'white';
        notificationElement.style.padding = '10px';
        notificationElement.style.borderRadius = '4px';
        notificationElement.style.animation = 'pulse 1s infinite';
    }
    
    // Browser notification
    if ("Notification" in window && Notification.permission === "granted") {
        const notification = new Notification("Mairie - File d'attente", {
            body: message,
            icon: 'images/logo.png',
            requireInteraction: isUrgent
        });
        
        if (!isUrgent) {
            setTimeout(() => notification.close(), 5000);
        }
    }
}

// Cancellation handler
async function handleCancellation(event) {
    event.preventDefault();
    
    const ticketNumber = sessionStorage.getItem('current_ticket');
    if (!ticketNumber) {
        showErrorAndRedirect('Impossible d\'identifier la réservation');
        return;
    }
    
    if (!confirm('Êtes-vous sûr de vouloir annuler cette réservation?')) {
        return;
    }
    
    try {
        const response = await fetch(`annuler_reservation.php?ticket=${ticketNumber}`);
        const result = await response.json();
        
        if (result.success) {
            showNotification('Réservation annulée avec succès');
            setTimeout(() => {
                window.location.href = 'reservation.html?canceled=1';
            }, 2000);
        } else {
            showNotification('Échec de l\'annulation: ' + (result.error || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Cancellation error:', error);
        showNotification('Erreur lors de l\'annulation');
    }
}

// Helper functions
function setupNotifications() {
    if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }
}

function getServiceName(serviceKey) {
    const services = {
        'etat-civil': 'État civil',
        'urbanisme': 'Urbanisme',
        'social': 'Services sociaux',
        'technique': 'Services techniques'
    };
    return services[serviceKey] || serviceKey;
}

function showErrorAndRedirect(message) {
    alert(message);
    redirectToReservationPage();
}

function redirectToReservationPage() {
    window.location.href = 'reservation.html';
}

// Add CSS animation for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
`;
document.head.appendChild(style);