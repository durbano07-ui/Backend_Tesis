<?php

use App\Models\Concerns\EncryptsHealthData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Complemento de 2026_10_21_000001_encrypt_health_data_at_rest.
 *
 * Esa migración dejó sin cifrar los textos vacíos (''), pero el cast "encrypted" intenta
 * descifrar todo valor distinto de NULL: una sola fila con '' hacía fallar con
 * DecryptException el listado completo (calendario de citas, motivos de consulta, etc.).
 * Aquí se cifran esos valores vacíos en todas las columnas que los modelos declaran cifradas.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->encryptedColumns() as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->where($column, '')->update([$column => Crypt::encryptString('')]);
            }
        }
    }

    public function down(): void
    {
        // Un '' cifrado se lee igual que antes; no hace falta revertir.
    }

    /** @return array<string, string[]> tabla => columnas cifradas */
    private function encryptedColumns(): array
    {
        $result = [];
        foreach (glob(app_path('Models/{,*/}*.php'), GLOB_BRACE) as $file) {
            $class = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], substr($file, strlen(app_path('Models/'))));
            if (!class_exists($class) || !in_array(EncryptsHealthData::class, class_uses_recursive($class), true)) {
                continue;
            }
            $table = (new $class())->getTable();
            if (!Schema::hasTable($table)) {
                continue;
            }
            $existing = Schema::getColumnListing($table);
            $columns = array_intersect($class::encryptedHealthFieldList(), $existing);
            $result[$table] = array_values(array_unique(array_merge($result[$table] ?? [], $columns)));
        }

        return $result;
    }
};
