document.addEventListener("DOMContentLoaded", () => {
    cargarTickets();

    const form = document.getElementById("newTicketForm");
    form.addEventListener("submit", crearTicket);
});

async function crearTicket(e) {
    e.preventDefault();

    const data = {
        titulo: document.getElementById("titulo").value,
        descripcion: document.getElementById("descripcion").value
    };

    await api(`${API_TICKETS}/tickets`, "POST", data);

    alert("Ticket creado");
    cargarTickets();
}

async function cargarTickets() {
    const lista = document.getElementById("listaTickets");
    const tickets = await api(`${API_TICKETS}/tickets`);

    lista.innerHTML = tickets
        .map(t => `<div class="ticket">
                <h4>${t.titulo}</h4>
                <p>${t.descripcion}</p>
                <a href="ticket-detail.html?id=${t.id}">Ver Detalle</a>
            </div>`
        )
        .join("");
}
