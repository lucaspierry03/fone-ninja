<?php

namespace App\Models;

use App\Enums\VendaStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    protected $fillable = ['cliente', 'total', 'lucro', 'status'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'lucro' => 'decimal:2',
            'status' => VendaStatus::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }
}
