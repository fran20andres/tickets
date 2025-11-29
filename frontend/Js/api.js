const API_USERS = "http://localhost:8001";
const API_TICKETS = "http://localhost:8002";

async function api(url, method = "GET", data = null, auth = true) {
    const headers = { "Content-Type": "application/json" };

    if (auth) {
        const token = localStorage.getItem("token");
        if (token) headers["Authorization"] = token;
    }

    const options = { method, headers };

    if (data) options.body = JSON.stringify(data);

    const res = await fetch(url, options);

    if (res.status === 401) {
        localStorage.removeItem("token");
        window.location = "login.html";
        return;
    }

    return res.json();
}
