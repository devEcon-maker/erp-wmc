<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'approved_by',
        'rejection_reason',
        'justification_path',
        'justification_name',
        'justification_type',
        'justification_size',
        'justification_uploaded_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'justification_uploaded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    // Accesseurs pour le justificatif
    public function getHasJustificationAttribute(): bool
    {
        return !empty($this->justification_path);
    }

    public function getJustificationUrlAttribute(): ?string
    {
        if (!$this->justification_path) {
            return null;
        }
        return Storage::url($this->justification_path);
    }

    public function getFormattedJustificationSizeAttribute(): string
    {
        if (!$this->justification_size) {
            return '-';
        }

        $bytes = $this->justification_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.2f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }

    // Supprimer le justificatif
    public function deleteJustification(): bool
    {
        if ($this->justification_path && Storage::disk('public')->exists($this->justification_path)) {
            Storage::disk('public')->delete($this->justification_path);
        }

        $this->update([
            'justification_path' => null,
            'justification_name' => null,
            'justification_type' => null,
            'justification_size' => null,
            'justification_uploaded_at' => null,
        ]);

        return true;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeForManager($query, $managerId)
    {
        return $query->whereHas('employee', function ($q) use ($managerId) {
            $q->where('manager_id', $managerId);
        });
    }
}
