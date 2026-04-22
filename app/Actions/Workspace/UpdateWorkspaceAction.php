<?php

namespace App\Actions\Workspace;

use App\Models\Workspace;

// WS-03
class UpdateWorkspaceAction
{
    /**
     * @param array<int, string> $countries
     */
    public function execute(
        Workspace $workspace,
        string $name,
        ?string $businessType = null,
        array $countries = [],
        string $defaultDialect = 'sa',
        ?string $logoPath = null,
    ): Workspace {
        $data = [
            'name'            => $name,
            'business_type'   => $businessType,
            'countries'       => $countries,
            'default_dialect' => $defaultDialect,
        ];

        if ($logoPath !== null) {
            $data['logo_path'] = $logoPath;
        }

        $workspace->update($data);

        return $workspace->fresh() ?? $workspace;
    }
}
