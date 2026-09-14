<?php

namespace Database\Seeders;

use App\Enums\SecretariatLevel;
use App\Models\Secretariat;
use Illuminate\Database\Seeder;

/**
 * The LTKL Secretariat structure. Rows are matched on name, so running this
 * again refreshes the sample rather than duplicating it.
 */
class SecretariatSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->members() as $index => $member) {
            Secretariat::updateOrCreate(
                ['name' => $member['name']],
                $member + [
                    'is_active' => true,
                    'sorted_at' => $index + 1,
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function members(): array
    {
        return [
            [
                'name' => 'Ristika Putri Istanti',
                'role_id' => 'Kepala Sekretariat LTKL',
                'level' => SecretariatLevel::TOP_LEVEL,
            ],
            [
                'name' => 'Adinda M. Aksari',
                'role_id' => 'Wakil Kepala Sekretariat LTKL',
                'level' => SecretariatLevel::TOP_LEVEL,
            ],
            [
                'name' => 'Julia Ikasarana',
                'role_id' => 'Manajer Perencanaan & Kebijakan',
                'level' => SecretariatLevel::MANAGER,
            ],
            [
                'name' => 'Dicky Hasian Zulkarnain',
                'role_id' => 'Manajer Bisnis Berkelanjutan',
                'level' => SecretariatLevel::MANAGER,
            ],
            [
                'name' => 'Desriko',
                'role_id' => 'Manajer Tata Kelola Anggota',
                'level' => SecretariatLevel::MANAGER,
            ],
            [
                'name' => 'Citra Clariza',
                'role_id' => 'Manajer Sumber Daya Manusia & Pengembangan Organisasi',
                'level' => SecretariatLevel::MANAGER,
            ],
            [
                'name' => 'Windi Hastari',
                'role_id' => 'Manajer Sumber Daya Keuangan',
                'level' => SecretariatLevel::MANAGER,
            ],
        ];
    }
}
