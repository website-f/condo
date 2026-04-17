<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('Packages')) {
            return;
        }

        foreach ($this->packages() as $package) {
            DB::table('Packages')->updateOrInsert(
                ['id' => $package['id']],
                $package
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('Packages')) {
            return;
        }

        DB::table('Packages')
            ->whereIn('id', [15, 16])
            ->delete();
    }

    /**
     * @return array<int, array<string, int|string|float>>
     */
    private function packages(): array
    {
        return [
            [
                'id' => 15,
                'name' => 'Condo Premium Package',
                'creditlimit' => 100,
                'maxaccount' => 500,
                'token' => 0,
                'icp_limit' => 500,
                'ipp_limit' => 500,
                'local_limit' => 500,
                'cost' => 360.00,
                'color' => '0xff0f766e',
                'is_unknown' => 0,
                'group_limit' => 0,
                'join_group_limit' => 0,
            ],
            [
                'id' => 16,
                'name' => 'Condo Premium Lite Package',
                'creditlimit' => 50,
                'maxaccount' => 100,
                'token' => 0,
                'icp_limit' => 100,
                'ipp_limit' => 100,
                'local_limit' => 100,
                'cost' => 180.00,
                'color' => '0xff2563eb',
                'is_unknown' => 0,
                'group_limit' => 0,
                'join_group_limit' => 0,
            ],
        ];
    }
};
