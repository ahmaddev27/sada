<?php

namespace App\Services\Admin;

use App\Models\AdminLog;
use Illuminate\Http\Request;

class AdminLogService
{
    public function __construct(private readonly Request $request) {}

    public function log(
        int $adminId,
        string $action,
        string $targetType = null,
        int $targetId = null,
        array $payload = [],
    ): void {
        AdminLog::create([
            'admin_id'    => $adminId,
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'payload'     => $payload ?: null,
            'ip'          => $this->request->ip(),
        ]);
    }
}
