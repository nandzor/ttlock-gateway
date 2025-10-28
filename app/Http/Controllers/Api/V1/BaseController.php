<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller {
    /**
     * Success response
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse {
        return ApiResponseHelper::success($data, $message, $code);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = 'Error', $errors = null, int $code = 400): JsonResponse {
        return ApiResponseHelper::error($message, 'ERROR', $errors, $code);
    }

    /**
     * Created response (201)
     */
    protected function createdResponse($data = null, string $message = 'Resource created successfully'): JsonResponse {
        return ApiResponseHelper::success($data, $message, 201);
    }

    /**
     * No content response (204)
     */
    protected function noContentResponse(): JsonResponse {
        return response()->json(null, 204);
    }

    /**
     * Not found response (404)
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse {
        return ApiResponseHelper::notFound($message);
    }

    /**
     * Unauthorized response (401)
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized'): JsonResponse {
        return ApiResponseHelper::unauthorized($message);
    }

    /**
     * Forbidden response (403)
     */
    protected function forbiddenResponse(string $message = 'Forbidden'): JsonResponse {
        return ApiResponseHelper::forbidden($message);
    }

    /**
     * Validation error response (422)
     */
    protected function validationErrorResponse($errors, string $message = 'Validation failed'): JsonResponse {
        return ApiResponseHelper::validationError($errors, $message);
    }

    /**
     * Server error response (500)
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse {
        return ApiResponseHelper::serverError($message);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($paginator, string $message = 'Success'): JsonResponse {
        return ApiResponseHelper::paginated($paginator, $message);
    }

    /**
     * Collection response
     */
    protected function collectionResponse($collection, string $message = 'Success'): JsonResponse {
        return ApiResponseHelper::success([
            'items' => $collection,
            'count' => count($collection),
        ], $message);
    }
}
