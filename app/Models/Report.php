<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'category_id', 'title', 'description', 'location', 'building', 'room', 'photo', 'status', 'note'])]
class Report extends Model
{
    protected $attributes = [
        'status' => 'dilaporkan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(ReportStatusLog::class)->latest();
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
