<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 20)->default('viewer')->after('email');
                $table->index('role');
            }
        });

        // Add CHECK constraint for role (PostgreSQL) - only if column was added
        if (!Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'operator', 'viewer'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            // Check if role column exists before trying to drop column
            if (Schema::hasColumn('users', 'role')) {
                // Drop CHECK constraint if it exists (PostgreSQL)
                try {
                    DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
                } catch (\Exception $e) {
                    // Constraint might not exist, continue
                }

                $table->dropColumn('role');
            }
        });
    }
};
