<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Produto extends Model
{
    protected $fillable = ['nome', 'preco_venda', 'custo_medio', 'estoque', 'imagem'];

    protected $appends = ['imagem_url'];

    protected function casts(): array
    {
        return [
            'preco_venda' => 'decimal:2',
            'custo_medio' => 'decimal:2',
            'estoque' => 'integer',
        ];
    }

    protected function imagemUrl(): Attribute
    {
        return Attribute::get(fn () => $this->imagem ? Storage::disk('s3')->url($this->imagem) : null);
    }

    public function compraItems(): HasMany
    {
        return $this->hasMany(CompraItem::class);
    }

    public function vendaItems(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }
}
