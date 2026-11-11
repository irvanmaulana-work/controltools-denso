<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User;
use App\Models\Stockout;
use App\Models\Partlist;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'created_by',
        'approver_id',
        'remark',
        'approval_remark',
        'status',
        'approved_at',
        'rejection_remark',
        'rejected_at',

        // Partlist fields (legacy snapshot)
        'requester',
        'line_requester',
        'partlist_name',
        'drawing_no_supplier',
        'size_type',
        'qty',
        'requested_qty',
        'received_qty',
        'sub_assy_drawing_unit',
        'location',

        // New: FK to master partlist so ticket can show latest data
        'partlist_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function partlist(): BelongsTo
    {
        return $this->belongsTo(Partlist::class, 'partlist_id');
    }

    public function stockouts(): HasMany
    {
        return $this->hasMany(Stockout::class);
    }
}