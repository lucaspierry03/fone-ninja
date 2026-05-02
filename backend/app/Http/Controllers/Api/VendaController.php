<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendaRequest;
use App\Http\Resources\VendaResource;
use App\Models\Venda;
use App\Repositories\VendaRepository;
use App\Services\VendaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendaController extends Controller
{
    public function __construct(
        protected VendaService $service,
        protected VendaRepository $repository,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return VendaResource::collection($this->repository->withItemsPaginated());
    }

    public function store(StoreVendaRequest $request): JsonResponse
    {
        $venda = $this->service->registrar($request->validated());
        return (new VendaResource($venda))->response()->setStatusCode(201);
    }

    public function cancelar(Venda $venda): VendaResource
    {
        return new VendaResource($this->service->cancelar($venda));
    }
}
