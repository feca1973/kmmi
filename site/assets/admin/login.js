import trianglify from 'trianglify';
import './login.css';

// Background pattern (as in the legacy login page). It is decoration only: the form works without it.
try {
    const pattern = trianglify({
        width: window.innerWidth || 1280,
        height: window.innerHeight || 720,
        cellSize: 50,
    });
    document.body.prepend(pattern.toCanvas());
} catch (error) {
    console.warn('Login background could not be drawn.', error);
}

const form = document.querySelector('form.login');
const feedback = form.querySelector('.feedback');
const submit = form.querySelector('.submit');

// The icon inside a field fades out while the field has the focus.
form.querySelectorAll('.input input:not([type=checkbox])').forEach((input) => {
    const icon = input.parentElement.querySelector('span');
    input.addEventListener('focus', () => {
        icon.classList.add('hide');
        feedback.classList.remove('show');
        submit.classList.remove('error');
    });
    input.addEventListener('blur', () => icon.classList.remove('hide'));
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) {
            throw new Error(`Login failed (${response.status})`);
        }

        const data = await response.json();
        setTimeout(() => {
            window.location.href = data.forwardUrl;
        }, 50);
    } catch (error) {
        feedback.classList.add('show');
        submit.classList.add('error');
    }
});
