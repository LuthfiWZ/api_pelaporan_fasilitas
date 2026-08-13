<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('building')->nullable()->after('location');
            $table->string('room')->nullable()->after('building');
            $table->string('status', 20)->default('dilaporkan')->change();
        });

        $defaultCategories = ['Listrik', 'AC', 'Sanitasi', 'Furniture', 'Lainnya'];
        foreach ($defaultCategories as $name) {
            DB::table('categories')->updateOrInsert(['name' => $name], ['created_at' => now(), 'updated_at' => now()]);
        }

        DB::table('reports')->orderBy('id')->get(['id', 'category'])->each(function ($row) {
            $name = $row->category ?: 'Lainnya';
            $categoryId = DB::table('categories')->where('name', $name)->value('id');
            if (! $categoryId) {
                $categoryId = DB::table('categories')->insertGetId([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('reports')->where('id', $row->id)->update(['category_id' => $categoryId]);
        });

        DB::table('reports')->where('status', 'menunggu')->update(['status' => 'dilaporkan']);
        DB::table('reports')->where('status', 'ditolak')->update(['status' => 'dilaporkan']);

        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'building', 'room']);
            $table->string('category')->nullable()->after('user_id');
            $table->enum('status', ['menunggu', 'diproses', 'selesai', 'ditolak'])->default('menunggu')->change();
        });
    }
};
