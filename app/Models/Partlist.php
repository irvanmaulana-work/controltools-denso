<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'drawing_no_supplier',
        'name',
        'size_type',
        'qty',
        'sub_assy_drawing_unit',
        'location',
    ];

    protected $casts = [
        'qty' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    // Scope stok menipis
    public function scopeLowStock($query)
    {
        return $query->where('qty', '<=', 1);
    }

    // Status helper
    public function isLowStock(): bool
    {
        return $this->qty <= 5;
    }

    public function isOutOfStock(): bool
    {
        return $this->qty <= 0;
    }
}