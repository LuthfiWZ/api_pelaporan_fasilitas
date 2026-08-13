<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['report_id', 'status', 'note', 'photo', 'user_id'])]
class ReportStatusLog extends Model
{
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'dilaporkan' => 'Dilaporkan',
            'diverifikasi' => 'Diverifikasi',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }
}
