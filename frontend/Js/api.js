async function api(url, method = "GET", data = null, auth = true) {
    const headers = { "Content-Type": "application/json" };

    if (auth) {
        const token = localStorage.getItem("token");
        if (token) headers["Authorization"] = token;
    }

    const options = { method, headers };

    // Body solo si hay data
    if (data) options.body = JSON.stringify(data);

    try {
        const res = await fetch(url, options);

        // Manejo de 401
        if (res.status === 401) {
            localStorage.removeItem("token");
            window.location = "login.html";
            return;
        }

        // Intentar convertir a JSON
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch {
            return { error: "Respuesta no es JSON", raw: text };
        }

    } catch (error) {
        console.error("Error de conexión:", error);
        return { error: "No se pudo conectar con el servidor" };
    }
}
