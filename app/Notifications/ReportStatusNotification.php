<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Report $report,
        public string $oldStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'report_title' => $this->report->title,
            'old_status' => $this->oldStatus,
            'old_status_label' => $this->statusLabel($this->oldStatus),
            'new_status' => $this->report->status,
            'new_status_label' => $this->report->statusLabel(),
            'message' => "Status laporan \"{$this->report->title}\" berubah menjadi {$this->report->statusLabel()}.",
        ];
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'dilaporkan' => 'Dilaporkan',
            'diverifikasi' => 'Diverifikasi',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            default => $status,
        };
    }
}
