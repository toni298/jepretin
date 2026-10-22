<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $columns = [
            ['users', 'id'],
            ['events', 'id'],
            ['events', 'user_id'],
            ['photos', 'id'],
            ['photos', 'event_id'],
            ['sessions', 'user_id'],
        ];

        $needsConversion = collect($columns)->contains(
            fn (array $column): bool => $this->isLegacyColumn($column[0], $column[1]),
        );

        if ($needsConversion) {
            DB::transaction(fn () => $this->convertLegacyIds());
        }
    }

    public function down(): void
    {
        throw new RuntimeException('UUID conversion cannot be safely reversed because original integer IDs are not retained.');
    }

    private function convertLegacyIds(): void
    {
        $this->createIdMap('users');
        $this->createIdMap('events');
        $this->createIdMap('photos');

        if ($this->isLegacyColumn('users', 'id')) {
            Schema::table('users', fn (Blueprint $table) => $table->uuid('uuid_id')->nullable());
            DB::statement('UPDATE users AS target SET uuid_id = mapping.new_id FROM pg_temp.jepretin_users_uuid_map AS mapping WHERE target.id::text = mapping.old_id');
        }

        if ($this->isLegacyColumn('events', 'id')) {
            Schema::table('events', fn (Blueprint $table) => $table->uuid('uuid_id')->nullable());
            DB::statement('UPDATE events AS target SET uuid_id = mapping.new_id FROM pg_temp.jepretin_events_uuid_map AS mapping WHERE target.id::text = mapping.old_id');
        }

        if ($this->isLegacyColumn('events', 'user_id')) {
            Schema::table('events', fn (Blueprint $table) => $table->uuid('uuid_user_id')->nullable());
            DB::statement('UPDATE events AS target SET uuid_user_id = mapping.new_id FROM pg_temp.jepretin_users_uuid_map AS mapping WHERE target.user_id::text = mapping.old_id');
        }

        if ($this->isLegacyColumn('photos', 'id')) {
            Schema::table('photos', fn (Blueprint $table) => $table->uuid('uuid_id')->nullable());
            DB::statement('UPDATE photos AS target SET uuid_id = mapping.new_id FROM pg_temp.jepretin_photos_uuid_map AS mapping WHERE target.id::text = mapping.old_id');
        }

        if ($this->isLegacyColumn('photos', 'event_id')) {
            Schema::table('photos', fn (Blueprint $table) => $table->uuid('uuid_event_id')->nullable());
            DB::statement('UPDATE photos AS target SET uuid_event_id = mapping.new_id FROM pg_temp.jepretin_events_uuid_map AS mapping WHERE target.event_id::text = mapping.old_id');
        }

        if ($this->isLegacyColumn('sessions', 'user_id')) {
            Schema::table('sessions', fn (Blueprint $table) => $table->uuid('uuid_user_id')->nullable());
            DB::statement('UPDATE sessions AS target SET uuid_user_id = mapping.new_id FROM pg_temp.jepretin_users_uuid_map AS mapping WHERE target.user_id::text = mapping.old_id');
        }

        if (Schema::hasTable('events')) {
            DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_user_id_foreign');
        }
        if (Schema::hasTable('photos')) {
            DB::statement('ALTER TABLE photos DROP CONSTRAINT IF EXISTS photos_event_id_foreign');
        }

        if ($this->isLegacyColumn('users', 'id')) {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_pkey');
            DB::statement('ALTER TABLE users RENAME COLUMN id TO legacy_id');
            DB::statement('ALTER TABLE users RENAME COLUMN uuid_id TO id');
            DB::statement('ALTER TABLE users ALTER COLUMN id SET NOT NULL');
            DB::statement('ALTER TABLE users DROP COLUMN legacy_id');
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_pkey PRIMARY KEY (id)');
            DB::statement('DROP SEQUENCE IF EXISTS users_id_seq');
        }

        if (Schema::hasTable('events')) {
            DB::statement('DROP INDEX IF EXISTS events_user_id_created_at_index');

            if ($this->isLegacyColumn('events', 'id')) {
                DB::statement('ALTER TABLE events DROP CONSTRAINT IF EXISTS events_pkey');
                DB::statement('ALTER TABLE events RENAME COLUMN id TO legacy_id');
                DB::statement('ALTER TABLE events RENAME COLUMN uuid_id TO id');
                DB::statement('ALTER TABLE events ALTER COLUMN id SET NOT NULL');
                DB::statement('ALTER TABLE events DROP COLUMN legacy_id');
                DB::statement('ALTER TABLE events ADD CONSTRAINT events_pkey PRIMARY KEY (id)');
                DB::statement('DROP SEQUENCE IF EXISTS events_id_seq');
            }

            if (Schema::hasColumn('events', 'uuid_user_id')) {
                DB::statement('ALTER TABLE events RENAME COLUMN user_id TO legacy_user_id');
                DB::statement('ALTER TABLE events RENAME COLUMN uuid_user_id TO user_id');
                DB::statement('ALTER TABLE events ALTER COLUMN user_id SET NOT NULL');
                DB::statement('ALTER TABLE events DROP COLUMN legacy_user_id');
            }

            DB::statement('CREATE INDEX events_user_id_created_at_index ON events (user_id, created_at)');
            DB::statement('ALTER TABLE events ADD CONSTRAINT events_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        }

        if (Schema::hasTable('photos')) {
            DB::statement('DROP INDEX IF EXISTS photos_event_id_status_index');

            if ($this->isLegacyColumn('photos', 'id')) {
                DB::statement('ALTER TABLE photos DROP CONSTRAINT IF EXISTS photos_pkey');
                DB::statement('ALTER TABLE photos RENAME COLUMN id TO legacy_id');
                DB::statement('ALTER TABLE photos RENAME COLUMN uuid_id TO id');
                DB::statement('ALTER TABLE photos ALTER COLUMN id SET NOT NULL');
                DB::statement('ALTER TABLE photos DROP COLUMN legacy_id');
                DB::statement('ALTER TABLE photos ADD CONSTRAINT photos_pkey PRIMARY KEY (id)');
                DB::statement('DROP SEQUENCE IF EXISTS photos_id_seq');
            }

            if (Schema::hasColumn('photos', 'uuid_event_id')) {
                DB::statement('ALTER TABLE photos RENAME COLUMN event_id TO legacy_event_id');
                DB::statement('ALTER TABLE photos RENAME COLUMN uuid_event_id TO event_id');
                DB::statement('ALTER TABLE photos ALTER COLUMN event_id SET NOT NULL');
                DB::statement('ALTER TABLE photos DROP COLUMN legacy_event_id');
            }

            DB::statement('CREATE INDEX photos_event_id_status_index ON photos (event_id, status)');
            DB::statement('ALTER TABLE photos ADD CONSTRAINT photos_event_id_foreign FOREIGN KEY (event_id) REFERENCES events (id) ON DELETE CASCADE');
        }

        if (Schema::hasColumn('sessions', 'uuid_user_id')) {
            DB::statement('DROP INDEX IF EXISTS sessions_user_id_index');
            DB::statement('ALTER TABLE sessions RENAME COLUMN user_id TO legacy_user_id');
            DB::statement('ALTER TABLE sessions RENAME COLUMN uuid_user_id TO user_id');
            DB::statement('ALTER TABLE sessions DROP COLUMN legacy_user_id');
            DB::statement('CREATE INDEX sessions_user_id_index ON sessions (user_id)');
        }

        DB::statement('DROP TABLE pg_temp.jepretin_users_uuid_map');
        DB::statement('DROP TABLE pg_temp.jepretin_events_uuid_map');
        DB::statement('DROP TABLE pg_temp.jepretin_photos_uuid_map');
    }

    private function createIdMap(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $mapName = "jepretin_{$table}_uuid_map";
        DB::statement("DROP TABLE IF EXISTS pg_temp.{$mapName}");
        $newId = $this->isLegacyColumn($table, 'id') ? 'gen_random_uuid()' : 'id::uuid';
        DB::statement("CREATE TEMP TABLE {$mapName} AS SELECT id::text AS old_id, {$newId} AS new_id FROM {$table}");
    }

    private function isLegacyColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table)
            && Schema::hasColumn($table, $column)
            && Schema::getColumnType($table, $column) !== 'uuid';
    }
};
