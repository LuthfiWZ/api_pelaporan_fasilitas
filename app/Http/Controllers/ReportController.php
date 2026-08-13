<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Notifications\ReportStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $reports = Report::with(['user', 'category'])
            ->when(! $user->isAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->query('category_id')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $search = $request->query('search');
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('building', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('building'), fn ($query) => $query->where('building', 'like', '%'.$request->query('building').'%'))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->query('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->query('to')))
            ->latest()
            ->get();

        return response()->json([
            'data' => ReportResource::collection($reports),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'room' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('reports', 'public');
        }

        $report = $request->user()->reports()->create($validated);

        $report->statusLogs()->create([
            'status' => 'dilaporkan',
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dikirim',
            'data' => new ReportResource($report->load(['user', 'category'])),
        ], 201);
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        $this->authorizeView($request->user(), $report);

        $report->load(['user', 'category', 'statusLogs.user']);

        return response()->json([
            'data' => new ReportResource($report),
        ]);
    }

    public function updateStatus(Request $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:dilaporkan,diverifikasi,diproses,selesai'],
            'note' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
        ]);

        $oldStatus = $report->status;
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports/progress', 'public');
        }

        $report->update([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? $report->note,
        ]);

        $report->statusLogs()->create([
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
            'photo' => $photoPath,
            'user_id' => $request->user()->id,
        ]);

        Notification::send($report->user, new ReportStatusNotification($report, $oldStatus));

        return response()->json([
            'message' => 'Status laporan diperbarui',
            'data' => new ReportResource($report->load(['user', 'category', 'statusLogs.user'])),
        ]);
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        if ($report->photo) {
            Storage::disk('public')->delete($report->photo);
        }

        foreach ($report->statusLogs as $log) {
            if ($log->photo) {
                Storage::disk('public')->delete($log->photo);
            }
        }

        $report->delete();

        return response()->json([
            'message' => 'Laporan dihapus',
        ]);
    }

    private function authorizeView($user, Report $report): void
    {
        if (! $user->isAdmin() && $report->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengakses laporan ini');
        }
    }
}
