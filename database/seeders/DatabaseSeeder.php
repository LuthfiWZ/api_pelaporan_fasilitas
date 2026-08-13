<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = [
            'Listrik' => 'Kerusakan instalasi, lampu, stop kontak, dan panel listrik',
            'AC' => 'Kerusakan AC, ventilasi, dan pendingin ruangan',
            'Sanitasi' => 'Kerusakan pipa air, wastafel, toilet, dan saluran',
            'Furniture' => 'Kerusakan meja, kursi, lemari, dan perabotan',
            'Fasilitas Umum' => 'Kerusakan fasilitas bersama seperti koridor dan lift',
            'Jaringan' => 'Kerusakan internet dan jaringan komputer',
            'Lainnya' => 'Kategori kerusakan lainnya',
        ];

        foreach ($categories as $name => $description) {
            Category::updateOrCreate(['name' => $name], ['description' => $description]);
        }

        User::firstOrCreate(
            ['email' => 'admin@pelaporan.test'],
            ['name' => 'Admin Fasilitas', 'password' => 'password123', 'role' => 'admin'],
        );

        User::firstOrCreate(
            ['email' => 'user@pelaporan.test'],
            ['name' => 'User Demo', 'password' => 'password123', 'role' => 'user'],
        );

        $this->seedSampleReports();
    }

    private function seedSampleReports(): void
    {
        $admin = User::where('email', 'admin@pelaporan.test')->first();
        $user = User::where('email', 'user@pelaporan.test')->first();

        $samples = [
            [
                'title' => 'Lampu koridor mati',
                'description' => 'Lampu di koridor lantai 2 mati sejak kemarin, area gelap.',
                'category' => 'Listrik',
                'building' => 'Gedung A',
                'room' => 'Koridor Lt 2',
                'status' => 'diproses',
                'created_at' => now()->subDays(2),
            ],
            [
                'title' => 'AC ruang rapat tidak dingin',
                'description' => 'AC hanya mengeluarkan angin biasa, tidak dingin sama sekali.',
                'category' => 'AC',
                'building' => 'Gedung B',
                'room' => 'Ruang Rapat Utama',
                'status' => 'selesai',
                'created_at' => now()->subMonths(2),
            ],
            [
                'title' => 'Toilet lantai 1 bocor',
                'description' => 'Air mengalir terus dari kloset dan lantai becek.',
                'category' => 'Sanitasi',
                'building' => 'Gedung C',
                'room' => 'Toilet Pria',
                'status' => 'diverifikasi',
                'created_at' => now()->subDays(5),
            ],
            [
                'title' => 'Kursi kuliah patah',
                'description' => 'Kaki kursi patah, membahayakan mahasiswa yang duduk.',
                'category' => 'Furniture',
                'building' => 'Gedung A',
                'room' => 'Ruang 101',
                'status' => 'dilaporkan',
                'created_at' => now()->subDay(),
            ],
            [
                'title' => 'Koneksi wifi sangat lambat',
                'description' => 'Internet putus-putus di area perpustakaan.',
                'category' => 'Jaringan',
                'building' => 'Gedung B',
                'room' => 'Perpustakaan',
                'status' => 'dilaporkan',
                'created_at' => now()->subMonths(3),
            ],
            [
                'title' => 'Lift berhenti mendadak',
                'description' => 'Lift berhenti di antara lantai 3 dan 4 selama 10 menit.',
                'category' => 'Fasilitas Umum',
                'building' => 'Gedung A',
                'room' => 'Lift 1',
                'status' => 'selesai',
                'created_at' => now()->subMonths(5),
            ],
            [
                'title' => 'Stop kontak hangus',
                'description' => 'Tercium bau hangus dari stop kontak di ruang server.',
                'category' => 'Listrik',
                'building' => 'Gedung C',
                'room' => 'Ruang Server',
                'status' => 'diproses',
                'created_at' => now()->subDays(4),
            ],
            [
                'title' => 'Pintu ruang lab rusak',
                'description' => 'Pintu sulit ditutup dan engselnya longgar.',
                'category' => 'Lainnya',
                'building' => 'Gedung A',
                'room' => 'Lab Komputer',
                'status' => 'selesai',
                'created_at' => now()->subMonth(),
            ],
        ];

        foreach ($samples as $sample) {
            $category = Category::where('name', $sample['category'])->first();
            if (! $category) {
                continue;
            }

            $createdAt = $sample['created_at'];

            $report = Report::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $sample['title'],
                'description' => $sample['description'],
                'location' => $sample['building'],
                'building' => $sample['building'],
                'room' => $sample['room'],
                'status' => $sample['status'],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $this->createStatusLogs($report, $sample['status'], $admin, $user, $createdAt);
        }
    }

    private function createStatusLogs(
        Report $report,
        string $status,
        User $admin,
        User $user,
        \Illuminate\Support\Carbon $createdAt,
    ): void {
        $notes = [
            'dilaporkan' => null,
            'diverifikasi' => 'Laporan diverifikasi oleh petugas.',
            'diproses' => 'Teknisi sedang menangani perbaikan.',
            'selesai' => 'Perbaikan selesai dilakukan.',
        ];

        $steps = match ($status) {
            'selesai' => ['dilaporkan', 'diverifikasi', 'diproses', 'selesai'],
            'diproses' => ['dilaporkan', 'diverifikasi', 'diproses'],
            'diverifikasi' => ['dilaporkan', 'diverifikasi'],
            default => ['dilaporkan'],
        };

        foreach ($steps as $index => $step) {
            $report->statusLogs()->create([
                'status' => $step,
                'note' => $notes[$step],
                'user_id' => $index === 0 ? $user->id : $admin->id,
                'created_at' => $createdAt->copy()->addHours($index + 1),
                'updated_at' => $createdAt->copy()->addHours($index + 1),
            ]);
        }
    }
}
