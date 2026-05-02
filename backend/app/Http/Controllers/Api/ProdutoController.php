<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use App\Repositories\ProdutoRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProdutoController extends Controller
{
    public function __construct(protected ProdutoRepository $repository) {}

    public function index(): AnonymousResourceCollection
    {
        return ProdutoResource::collection($this->repository->paginate());
    }

    public function all(): JsonResponse
    {
        return response()->json(
            $this->repository->all()->map(fn ($p) => [
                'id' => $p->id,
                'nome' => $p->nome,
                'preco_venda' => $p->preco_venda,
                'custo_medio' => $p->custo_medio,
                'estoque' => $p->estoque,
            ])
        );
    }

    public function store(StoreProdutoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('', 's3');
        }

        return (new ProdutoResource($this->repository->create($data)))->response()->setStatusCode(201);
    }

    public function update(StoreProdutoRequest $request, Produto $produto): ProdutoResource
    {
        $data = $request->validated();

        if ($request->hasFile('imagem')) {
            $data['imagem'] = $request->file('imagem')->store('', 's3');
        }

        $this->repository->update($produto->id, $data);

        return new ProdutoResource($produto->fresh());
    }

    public function destroy(Produto $produto): JsonResponse
    {
        $this->repository->delete($produto->id);

        return response()->json(null, 204);
    }
}
