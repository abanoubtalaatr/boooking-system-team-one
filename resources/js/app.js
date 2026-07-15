/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

const shell = document.querySelector('[data-shell]');

document.querySelectorAll('[data-menu-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        shell?.classList.toggle('is-menu-open');
        button.setAttribute('aria-expanded', shell?.classList.contains('is-menu-open') ? 'true' : 'false');
    });
});

document.querySelector('[data-overlay]')?.addEventListener('click', () => shell?.classList.remove('is-menu-open'));
document.querySelector('[data-collapse]')?.addEventListener('click', () => shell?.classList.toggle('is-collapsed'));

document.querySelector('[data-profile-toggle]')?.addEventListener('click', (event) => {
    const menu = document.querySelector('[data-profile-menu]');
    const isOpen = menu?.classList.toggle('is-open');
    event.currentTarget.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
});

document.querySelector('[data-password-toggle]')?.addEventListener('click', (event) => {
    const input = document.querySelector('#password');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    event.currentTarget.setAttribute('aria-label', input.type === 'password' ? 'Show password' : 'Hide password');
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        shell?.classList.remove('is-menu-open');
        document.querySelector('[data-profile-menu]')?.classList.remove('is-open');
    }
});
