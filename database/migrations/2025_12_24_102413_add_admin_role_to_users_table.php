<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the column
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN for ENUM, so we'll use a workaround
            // This will work for development. For production with MySQL/PostgreSQL, use ALTER TABLE
            DB::statement("PRAGMA foreign_keys=off");
            DB::statement("CREATE TABLE users_new AS SELECT * FROM users");
            DB::statement("DROP TABLE users");
            DB::statement("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at DATETIME NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'tenant' CHECK(role IN ('tenant', 'owner', 'manager', 'admin')),
                phone VARCHAR(255) NULL,
                address TEXT NULL,
                two_factor_secret TEXT NULL,
                two_factor_recovery_codes TEXT NULL,
                two_factor_confirmed_at DATETIME NULL,
                remember_token VARCHAR(100) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");
            DB::statement("INSERT INTO users SELECT * FROM users_new");
            DB::statement("DROP TABLE users_new");
            DB::statement("PRAGMA foreign_keys=on");
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tenant', 'owner', 'manager', 'admin') DEFAULT 'tenant'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Revert to original enum values
            DB::statement("PRAGMA foreign_keys=off");
            DB::statement("CREATE TABLE users_new AS SELECT * FROM users");
            DB::statement("DROP TABLE users");
            DB::statement("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                email_verified_at DATETIME NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'tenant' CHECK(role IN ('tenant', 'owner', 'manager')),
                phone VARCHAR(255) NULL,
                address TEXT NULL,
                two_factor_secret TEXT NULL,
                two_factor_recovery_codes TEXT NULL,
                two_factor_confirmed_at DATETIME NULL,
                remember_token VARCHAR(100) NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )");
            DB::statement("INSERT INTO users SELECT * FROM users_new WHERE role != 'admin'");
            DB::statement("DROP TABLE users_new");
            DB::statement("PRAGMA foreign_keys=on");
        } else {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tenant', 'owner', 'manager') DEFAULT 'tenant'");
        }
    }
};
