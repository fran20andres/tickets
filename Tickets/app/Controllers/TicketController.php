<?php
namespace App\Controllers;

use App\Models\Ticket;
use App\Models\TicketActividad;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TicketController
{
    // ===================================================
    // 1. CREAR TICKET (SOLO GESTORES)
    // ===================================================
    public function crearTicket(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        if (!$user || $user->role !== 'gestor') {
            return $this->json($response, ['error' => 'Acceso denegado, solo gestores'], 403);
        }

        $data = (array) $request->getParsedBody();

        if (empty($data['titulo']) || empty($data['descripcion'])) {
            return $this->json($response, ['error' => 'Campos requeridos: titulo, descripcion'], 400);
        }

        $ticket = Ticket::create([
            'titulo'      => $data['titulo'],
            'descripcion' => $data['descripcion'],
            'gestor_id'   => $user->id,
            'estado'      => 'abierto',
            'admin_id'    => null
        ]);

        TicketActividad::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'mensaje'   => 'Ticket creado'
        ]);

        return $this->json($response, ['ticket' => $ticket], 201);
    }

    // ===================================================
    // 2. LISTAR TICKETS
    // ===================================================
    public function listarTickets(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('user');

        if ($user->role === 'admin') {
            $tickets = Ticket::all();
        } else {
            $tickets = Ticket::where('gestor_id', $user->id)->get();
        }

        return $this->json($response, ['tickets' => $tickets]);
    }

    // ===================================================
    // 3. DETALLE TICKET
    // ===================================================
    public function detalleTicket(Request $request, Response $response, array $args): Response
    {
        $ticket = Ticket::with('actividades')->find($args['id']);

        if (!$ticket) {
            return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
        }

        return $this->json($response, ['ticket' => $ticket]);
    }

    // ===================================================
    // 4. ACTUALIZAR TICKET (SOLO ADMIN)
    // ===================================================
    public function actualizarTicket(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');

        if ($user->role !== 'admin') {
            return $this->json($response, ['error' => 'Acceso denegado, solo administradores'], 403);
        }

        $ticket = Ticket::find($args['id']);

        if (!$ticket) {
            return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
        }

        $data = (array) $request->getParsedBody();

        $ticket->update($data);

        return $this->json($response, ['ticket' => $ticket]);
    }

    // ===================================================
    // 5. AGREGAR COMENTARIO
    // ===================================================
    public function agregarComentario(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('user');

        $ticket = Ticket::find($args['id']);

        if (!$ticket) {
            return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
        }

        $data = (array) $request->getParsedBody();

        $actividad = TicketActividad::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'mensaje'   => $data['mensaje']
        ]);

        return $this->json($response, ['actividad' => $actividad], 201);
    }

    private function json(Response $response, $payload, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
