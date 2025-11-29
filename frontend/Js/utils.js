// Validar campos obligatorios
function validateForm(formData) {
    for (let key in formData) {
        if (!formData[key]) {
            alert(`${key} es obligatorio`);
            return false;
        }
    }
    return true;
}

// Mostrar/ocultar elementos
function showElement(selector) {
    document.querySelector(selector).classList.remove('hidden');
}

function hideElement(selector) {
    document.querySelector(selector).classList.add('hidden');
}

// Redirigir a página
function redirectTo(page) {
    window.location.href = page;
}