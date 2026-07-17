<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Estandariza el formato de todas las respuestas JSON de la API:
 * { "success": bool, "message": string, "data": mixed }
 *
 * Funciona tanto con datos simples (arrays, modelos) como con Resources y
 * ResourceCollection: al incluirlos en el payload, Laravel los serializa
 * correctamente porque JsonResource implementa JsonSerializable.
 */
trait ApiResponser
{
    protected function success(mixed $data = null, string $message = 'Operacion realizada correctamente', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function created(mixed $data = null, string $message = 'Recurso creado correctamente'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Respuesta para colecciones paginadas: agrega un bloque "meta" con la
     * informacion de paginacion, ya que al envolver un ResourceCollection
     * dentro de "data" se pierde el wrapping automatico de Laravel
     * (links/meta solo se agregan cuando el Resource se retorna directamente).
     *
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @param  string|null  $resourceClass  Clase de JsonResource a aplicar a cada item (opcional).
     */
    protected function paginated($paginator, ?string $resourceClass = null, string $message = 'Operacion realizada correctamente'): JsonResponse
    {
        $items = $resourceClass ? $resourceClass::collection($paginator->getCollection()) : $paginator->getCollection();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 200);
    }

    protected function noContentMessage(string $message = 'Operacion realizada correctamente'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => null,
        ], 200);
    }

    protected function error(string $message = 'Ocurrio un error', int $code = 400, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }
}
