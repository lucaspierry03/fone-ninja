<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\VendaController;
use Illuminate\Support\Facades\Route;

Route::get('produtos/all', [ProdutoController::class, 'all']);
Route::apiResource('produtos', ProdutoController::class)->only(['index', 'store', 'update', 'destroy']);
Route::apiResource('compras', CompraController::class)->only(['index', 'store']);
Route::apiResource('vendas', VendaController::class)->only(['index', 'store']);
Route::patch('vendas/{venda}/cancelar', [VendaController::class, 'cancelar']);
Route::get('audit-logs', [AuditLogController::class, 'index']);
