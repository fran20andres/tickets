async function login(e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const pass = document.getElementById("password").value;

    const data = await api(`${API_USERS}/login`, "POST", { email, password: pass }, false);

    if (data.token) {
        localStorage.setItem("token", data.token);
        localStorage.setItem("role", data.user.role); // <-- IMPORTANTE, guarda rol
        localStorage.setItem("user_id", data.user.id);

        // Ahora todos van a la misma página
        window.location = "usuarios.html";

    } else {
        alert("Credenciales incorrectas");
    }
}

async function register(e) {
    e.preventDefault();

    const payload = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        password: document.getElementById("password").value
    };

    const data = await api(`${API_USERS}/register`, "POST", payload, false);

    if (data.error) {
        alert(data.error);
        return;
    }

    alert("Registro exitoso");
    window.location = "login.html";
}
