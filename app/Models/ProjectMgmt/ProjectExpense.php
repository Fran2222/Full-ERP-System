<?php

namespace App\Models\ProjectMgmt;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectExpense extends Model
{
    protected $table = 'project_expenses';
    use HasFactory;

    protected $fillable = [
        'project_rfp_id',
        'store_name_id',
        'receipts_total_amount',
        'status',
        'attachment_path',
        'attachment_original_name',
        'attachment_mime_type',
        'attachment_size',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receipts_total_amount' => 'decimal:2',
    ];

    public function rfp(): BelongsTo
    {
        return $this->belongsTo(ProjectRfp::class, 'project_rfp_id');
    }

    public function storeName(): BelongsTo
    {
        return $this->belongsTo(StoreName::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ProjectExpenseReceipt::class, 'project_expense_id', 'id')->orderBy('store_receipt_date')->orderBy('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function statusLabel(): Attribute
    {
        return Attribute::get(fn () => ucfirst($this->status ?? 'pending'));
    }
}
