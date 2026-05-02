<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Resources\CompraResource;
use App\Repositories\CompraRepository;
use App\Services\CompraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompraController extends Controller
{
    public function __construct(
        protected CompraService $service,
        protected CompraRepository $repository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return CompraResource::collection($this->repository->withItemsPaginated());
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $compra = $this->service->registrar($request->validated());
        return (new CompraResource($compra))->response()->setStatusCode(201);
    }
}
