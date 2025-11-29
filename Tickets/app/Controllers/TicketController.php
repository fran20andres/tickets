<?php
namespace App\Controllers;

use App\Models\Ticket;
use App\Models\TicketActividad;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TicketController
{
    /**
     * Crear ticket (solo role = gestor)
     */
    public function crearTicket(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            if (!$user || ($user->role ?? '') !== 'gestor') {
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
                'estado'      => $data['estado'] ?? 'abierto',
                'admin_id'    => $data['admin_id'] ?? null
            ]);

            TicketActividad::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $user->id,
                'mensaje'   => 'Ticket creado'
            ]);

            return $this->json($response, ['ticket' => $ticket], 201);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Error al crear ticket', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar tickets (admin ve todos, gestor solo los suyos)
     */
    public function listarTickets(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            if (!$user) {
                return $this->json($response, ['error' => 'Usuario no autenticado'], 401);
            }

            // Soporta filtros por query params: estado, gestor_id, admin_id
            $params = $request->getQueryParams();

            if ($user->role === 'admin') {
                $query = Ticket::query();
            } else {
                $query = Ticket::where('gestor_id', $user->id);
            }

            if (!empty($params['estado'])) {
                $query->where('estado', $params['estado']);
            }
            if (!empty($params['gestor_id'])) {
                $query->where('gestor_id', (int)$params['gestor_id']);
            }
            if (!empty($params['admin_id'])) {
                $query->where('admin_id', (int)$params['admin_id']);
            }

            $tickets = $query->get();

            return $this->json($response, ['tickets' => $tickets], 200);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Error al listar tickets', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Detalle de ticket con actividades
     */
    public function detalleTicket(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) ($args['id'] ?? 0);
            if ($id <= 0) {
                return $this->json($response, ['error' => 'ID inválido'], 400);
            }

            $ticket = Ticket::with('actividades')->find($id);
            if (!$ticket) {
                return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
            }

            return $this->json($response, ['ticket' => $ticket], 200);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Error al obtener ticket', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar ticket (solo admin)
     */
    public function actualizarTicket(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            if (!$user || ($user->role ?? '') !== 'admin') {
                return $this->json($response, ['error' => 'Acceso denegado, solo administradores'], 403);
            }

            $id = (int) ($args['id'] ?? 0);
            if ($id <= 0) {
                return $this->json($response, ['error' => 'ID inválido'], 400);
            }

            $ticket = Ticket::find($id);
            if (!$ticket) {
                return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
            }

            $data = (array) $request->getParsedBody();

            // Validar campos permitidos si lo deseas
            $allowed = ['titulo', 'descripcion', 'estado', 'admin_id'];
            $updateData = [];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) $updateData[$f] = $data[$f];
            }

            $ticket->update($updateData);

            // Registrar actividad opcional si se envía mensaje
            if (!empty($data['mensaje'])) {
                TicketActividad::create([
                    'ticket_id' => $ticket->id,
                    'user_id'   => $user->id,
                    'mensaje'   => $data['mensaje']
                ]);
            }

            return $this->json($response, ['ticket' => $ticket], 200);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Error al actualizar ticket', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Agregar comentario a ticket (gestor o admin)
     */
    public function agregarComentario(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            if (!$user) {
                return $this->json($response, ['error' => 'Usuario no autenticado'], 401);
            }

            $id = (int) ($args['id'] ?? 0);
            if ($id <= 0) {
                return $this->json($response, ['error' => 'ID inválido'], 400);
            }

            $ticket = Ticket::find($id);
            if (!$ticket) {
                return $this->json($response, ['error' => 'Ticket no encontrado'], 404);
            }

            $data = (array) $request->getParsedBody();
            $mensaje = trim($data['mensaje'] ?? '');

            if ($mensaje === '') {
                return $this->json($response, ['error' => 'Mensaje requerido'], 400);
            }

            $actividad = TicketActividad::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $user->id,
                'mensaje'   => $mensaje
            ]);

            return $this->json($response, ['actividad' => $actividad], 201);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => 'Error al agregar comentario', 'details' => $e->getMessage()], 500);
        }
    }

    /* ---------------- Helpers ---------------- */

    /**
     * Respuesta JSON estándar
     */
    private function json(Response $response, $payload, int $status = 200): Response
    {
        $body = $response->getBody();
        $body->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
