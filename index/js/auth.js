// auth.js - Shared across all pages
const AUTH_STORAGE_KEY = 'isLoggedIn';

function isLoggedIn() {
  return localStorage.getItem(AUTH_STORAGE_KEY) === 'true';
}

function setAuthState(isAuthenticated) {
  localStorage.setItem(AUTH_STORAGE_KEY, String(isAuthenticated));
}

function syncAuthUI() {
  const authenticated = isLoggedIn();
  const linksToToggle = [
    { id: 'login-link', visible: !authenticated },
    { id: 'signup-link', visible: !authenticated },
    { id: 'profile-link', visible: authenticated },
    { id: 'logout-link', visible: authenticated }
  ];

  linksToToggle.forEach(({ id, visible }) => {
    const element = document.getElementById(id);
    if (element) {
      element.style.display = visible ? 'inline-block' : 'none';
    }
  });
}

function login() {
  setAuthState(true);
  syncAuthUI();

  const fallbackPage = document.getElementById('profile-link') ? 'profile.html' : 'reservation.html';
  window.location.replace(fallbackPage);
}

function logout() {
  setAuthState(false);
  syncAuthUI();
  window.location.replace('index.html');
}

document.addEventListener('DOMContentLoaded', () => {
  syncAuthUI();

  const logoutLink = document.getElementById('logout-link');
  if (logoutLink) {
    logoutLink.addEventListener('click', (event) => {
      event.preventDefault();
      logout();
    });
  }

  document.addEventListener('click', (event) => {
    const logoutTrigger = event.target.closest('[data-auth-action="logout"]');
    if (logoutTrigger) {
      event.preventDefault();
      logout();
    }
  });

  window.addEventListener('storage', syncAuthUI);
});