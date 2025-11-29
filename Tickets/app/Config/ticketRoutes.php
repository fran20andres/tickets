<?php

use App\Controllers\TicketController;
use App\Middleware\AuthMiddleware;

$app->group("/tickets", function ($group) {

    $group->post("", [TicketController::class, "crearTicket"]);
    $group->get("", [TicketController::class, "listarTickets"]);
    $group->get("/{id}", [TicketController::class, "detalleTicket"]);
    $group->put("/{id}", [TicketController::class, "actualizarTicket"]);
    $group->post("/{id}/comentarios", [TicketController::class, "agregarComentario"]);

})->add(new AuthMiddleware());
