document.addEventListener("DOMContentLoaded", () => {

    const loginForm = document.getElementById("loginForm");
    const regForm = document.getElementById("regForm");

    if (loginForm) loginForm.addEventListener("submit", login);
    if (regForm) regForm.addEventListener("submit", register);
});

async function login(e) {
    e.preventDefault();

    const email = document.getElementById("email").value;
    const pass = document.getElementById("password").value;

    const data = await api(`${API_USERS}/login`, "POST", { email, password: pass }, false);

    if (data.token) {
        localStorage.setItem("token", data.token);

        if (data.user.role === "gestor") {
            window.location = "dashboard-gestor.html";
        } else {
            window.location = "dashboard-admin.html";
        }
    } else {
        alert("Credenciales incorrectas");
    }
}

async function register(e) {
    e.preventDefault();

    const data = {
        name: document.getElementById("name").value,
        email: document.getElementById("email").value,
        password: document.getElementById("password").value
    };

    const res = await api(`${API_USERS}/register`, "POST", data, false);

    alert("Registro exitoso");
    window.location = "login.html";
}

function logout() {
    localStorage.removeItem("token");
    window.location = "login.html";
}
