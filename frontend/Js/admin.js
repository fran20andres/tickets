document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();
    cargarTickets();
});

async function cargarUsuarios() {
    const lista = document.getElementById("listaUsuarios");
    const data = await api(`${API_USERS}/users`);

    lista.innerHTML = data
        .map(user => `
            <div class="card">
                <p><b>${user.name}</b> (${user.role})</p>
                <p>${user.email}</p>
            </div>
        `)
        .join("");
}

async function cargarTickets() {
    const lista = document.getElementById("listaTickets");
    const data = await api(`${API_TICKETS}/tickets`);

    lista.innerHTML = data
        .map(t => `
            <div class="ticket">
                <h4>${t.titulo}</h4>
                <p>${t.descripcion}</p>
                <p>Estado: ${t.estado}</p>
                <a href="ticket-detail.html?id=${t.id}">Ver detalle</a>
            </div>
        `)
        .join("");
}
