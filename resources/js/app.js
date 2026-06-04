import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const fullNamePattern = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*(?:\s+[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*)+$/;
const repeatedPhonePattern = /^(\d)\1{9}$/;
const sequentialPhones = new Set(['0123456789', '1234567890', '9876543210', '0987654321']);

document.addEventListener('input', (event) => {
    const field = event.target;

    if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement)) {
        return;
    }

    if (field.matches('[data-digits-only]')) {
        field.value = field.value.replace(/\D/g, '').slice(0, 10);

        if (field.value.length === 10 && (repeatedPhonePattern.test(field.value) || sequentialPhones.has(field.value))) {
            field.setCustomValidity('Escribe un teléfono real de 10 dígitos.');
        } else {
            field.setCustomValidity('');
        }
    }

    if (field.matches('[data-full-name]')) {
        const value = field.value.trim().replace(/\s+/g, ' ');
        field.setCustomValidity(value === '' || fullNamePattern.test(value)
            ? ''
            : 'Escribe nombre completo, solo con letras y espacios.');
    }

    if (field.matches('[data-no-long-digits]')) {
        field.setCustomValidity(/\d{6,}/.test(field.value)
            ? 'No escribas números continuos en las condiciones iniciales.'
            : '');
    }
});
