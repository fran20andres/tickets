document.addEventListener("DOMContentLoaded", cargarDetalle);

async function cargarDetalle() {
    const id = new URLSearchParams(window.location.search).get("id");

    const ticket = await api(`${API_TICKETS}/tickets/${id}`);

    document.getElementById("ticketDetalle").innerHTML = `
        <h3>${ticket.titulo}</h3>
        <p>${ticket.descripcion}</p>
        <p><b>Estado:</b> ${ticket.estado}</p>
        <h4>Comentarios:</h4>
        <ul>
            ${ticket.actividades.map(a => `<li><b>${a.user_id}</b>: ${a.mensaje}</li>`).join("")}
        </ul>
    `;
}

async function agregarComentario() {
    const id = new URLSearchParams(window.location.search).get("id");
    const mensaje = document.getElementById("mensaje").value;

    await api(`${API_TICKETS}/tickets/${id}/comentarios`, "POST", { mensaje });

    alert("Comentario agregado");
    location.reload();
}
