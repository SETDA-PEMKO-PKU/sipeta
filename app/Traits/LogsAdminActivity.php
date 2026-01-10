<?php

namespace App\Traits;

use App\Models\AdminActivityLog;

trait LogsAdminActivity
{
    /**
     * Log a create activity
     */
    protected function logCreate($model, ?string $description = null): void
    {
        $module = $this->getModuleFromModel($model);
        $modelName = class_basename($model);
        
        AdminActivityLog::log(
            AdminActivityLog::ACTION_CREATE,
            $module,
            $description ?? "Membuat {$modelName} baru: " . ($model->nama ?? $model->name ?? $model->id),
            $model,
            null,
            $model->toArray()
        );
    }

    /**
     * Log an update activity
     */
    protected function logUpdate($model, array $oldData, ?string $description = null): void
    {
        $module = $this->getModuleFromModel($model);
        $modelName = class_basename($model);
        
        AdminActivityLog::log(
            AdminActivityLog::ACTION_UPDATE,
            $module,
            $description ?? "Mengubah {$modelName}: " . ($model->nama ?? $model->name ?? $model->id),
            $model,
            $oldData,
            $model->toArray()
        );
    }

    /**
     * Log a delete activity
     */
    protected function logDelete($model, ?string $description = null): void
    {
        $module = $this->getModuleFromModel($model);
        $modelName = class_basename($model);
        
        AdminActivityLog::log(
            AdminActivityLog::ACTION_DELETE,
            $module,
            $description ?? "Menghapus {$modelName}: " . ($model->nama ?? $model->name ?? $model->id),
            $model,
            $model->toArray(),
            null
        );
    }

    /**
     * Log a view activity
     */
    protected function logView($model, ?string $description = null): void
    {
        $module = $this->getModuleFromModel($model);
        $modelName = class_basename($model);
        
        AdminActivityLog::log(
            AdminActivityLog::ACTION_VIEW,
            $module,
            $description ?? "Melihat {$modelName}: " . ($model->nama ?? $model->name ?? $model->id),
            $model
        );
    }

    /**
     * Log an export activity
     */
    protected function logExport(string $module, ?string $description = null): void
    {
        AdminActivityLog::log(
            AdminActivityLog::ACTION_EXPORT,
            $module,
            $description ?? "Export data {$module}"
        );
    }

    /**
     * Log an import activity
     */
    protected function logImport(string $module, ?string $description = null, ?array $newData = null): void
    {
        AdminActivityLog::log(
            AdminActivityLog::ACTION_IMPORT,
            $module,
            $description ?? "Import data {$module}",
            null,
            null,
            $newData
        );
    }

    /**
     * Get module name from model class
     */
    protected function getModuleFromModel($model): string
    {
        $className = class_basename($model);
        
        return match($className) {
            'Opd' => AdminActivityLog::MODULE_OPD,
            'Jabatan' => AdminActivityLog::MODULE_JABATAN,
            'Asn' => AdminActivityLog::MODULE_ASN,
            'Admin' => AdminActivityLog::MODULE_ADMIN,
            default => strtolower($className),
        };
    }
}
