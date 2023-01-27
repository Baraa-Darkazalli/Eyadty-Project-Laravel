<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponderTrait
{
    public function success($data, $message = null, $statusCode = Response::HTTP_OK): JsonResponse
    {
        if (! $message) {
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json([
                'message' => $message,
                'data' => $data,
            ], $statusCode);
    }

    public function error($message = null, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if (! $message) {
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json([
            'message' => $message,
        ], $statusCode);
    }

    public function noContent($message = ''): JsonResponse
    {
        return $this->success(null, $message, Response::HTTP_NO_CONTENT);
    }

    public function unauthorizedResponse($message = ''): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    public function forbiddenResponse($message = ''): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    public function badRequestResponse($message = ''): JsonResponse
    {
        return $this->error($message, Response::HTTP_BAD_REQUEST);
    }

    public function createdResponse($data, $message = ''): JsonResponse
    {
        return $this->success($data, $message, Response::HTTP_CREATED);
    }

    public function okResponse($data, $message = ''): JsonResponse
    {
        return $this->success($data, $message);
    }
    public function notFoundResponse($message = ''): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    //This is many functions we should uncomment it when we need it.


    // public function conflictResponse($message = ''): JsonResponse
    // {
    //     return $this->error($message, Response::HTTP_CONFLICT);
    // }

    // public function unprocessableResponse($message = ''): JsonResponse
    // {
    //     return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY);
    // }
}
