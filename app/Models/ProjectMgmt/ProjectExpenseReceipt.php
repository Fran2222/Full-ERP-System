<?php

namespace App\Models\ProjectMgmt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectExpenseReceipt extends Model
{
    protected $table = 'project_expense_receipts';
    use HasFactory;

    protected $fillable = [
        'project_expense_id',
        'store_receipt_no',
        'store_receipt_date',
        'receipts_total_amount',
        'remarks',
    ];

    protected $casts = [
        'store_receipt_date' => 'date',
        'receipts_total_amount' => 'decimal:2',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(ProjectExpense::class, 'project_expense_id');
    }
}
