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

const profileToggle = document.querySelector('[data-profile-toggle]');
const profileMenu = document.querySelector('[data-profile-menu]');

const closeProfileMenu = (restoreFocus = false) => {
    if (!profileToggle || !profileMenu) return;

    profileMenu.classList.remove('is-open');
    profileMenu.hidden = true;
    profileToggle.setAttribute('aria-expanded', 'false');

    if (restoreFocus) profileToggle.focus();
};

profileToggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    if (!profileMenu) return;

    const isOpening = profileMenu.hidden;
    profileMenu.hidden = !isOpening;
    profileMenu.classList.toggle('is-open', isOpening);
    profileToggle.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
});

document.addEventListener('click', (event) => {
    if (!profileMenu || profileMenu.hidden) return;
    if (profileMenu.contains(event.target) || profileToggle?.contains(event.target)) return;

    closeProfileMenu();
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
        closeProfileMenu(true);
    }
});

document.querySelectorAll('[data-permission-groups]').forEach((container) => {
    container.querySelectorAll('[data-permission-group]').forEach((groupCheckbox) => {
        const items = [...container.querySelectorAll(`[data-permission-item="${groupCheckbox.dataset.permissionGroup}"]`)];
        const refreshGroupState = () => {
            const checkedCount = items.filter((item) => item.checked).length;
            groupCheckbox.checked = items.length > 0 && checkedCount === items.length;
            groupCheckbox.indeterminate = checkedCount > 0 && checkedCount < items.length;
        };

        groupCheckbox.addEventListener('change', () => {
            items.forEach((item) => {
                if (!item.disabled) item.checked = groupCheckbox.checked;
            });
            refreshGroupState();
        });
        items.forEach((item) => item.addEventListener('change', refreshGroupState));
        refreshGroupState();
    });
});
