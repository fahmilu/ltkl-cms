<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Pillar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PillarSeeder extends Seeder
{
    /**
     * Seed three pillars, matching the first entries of the pillar taxonomy.
     *
     * Practices and pillar attachments are only created when kabupatens already
     * exist, so this seeder is safe to run against an empty database.
     */
    public function run(): void
    {
        $kabupatens = Kabupaten::orderBy('sorted_at')->get();
        $index = 0;

        foreach ($this->pillars() as $definition) {
            $index++;

            $practices = $definition['practices'];
            unset($definition['practices']);

            $pillar = Pillar::updateOrCreate(
                ['slug' => $definition['slug']],
                array_merge($definition, [
                    'is_active' => true,
                    'sorted_at' => $index,
                ])
            );

            // Anchor each example to a kabupaten, cycling through whatever exists.
            $pillar->practices()->delete();

            if ($kabupatens->isNotEmpty()) {
                foreach ($practices as $position => $practice) {
                    $pillar->practices()->create(array_merge($practice, [
                        'kabupaten_id' => $kabupatens[$position % $kabupatens->count()]->id,
                        'sorted_at' => $position + 1,
                    ]));
                }

                // Every kabupaten relates to the first two pillars, so the
                // "Pilar Terkait" section has something to show.
                if ($index <= 2) {
                    $pillar->kabupatens()->sync($kabupatens->pluck('id'));
                }
            }
        }

        $this->command?->info('Seeded ' . $index . ' pillars.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pillars(): array
    {
        return [
            [
                'title' => 'Shared governance',
                'title_id' => 'Tata kelola bersama',
                'slug' => Str::slug('Shared governance'),
                'slug_id' => Str::slug('Tata kelola bersama'),
                'technical_term' => 'Multi-stakeholder governance',
                'technical_term_id' => 'Tata kelola multipihak',
                'description' => 'Government, citizens and business make decisions at one table, working from the same data and the same targets.',
                'description_id' => 'Pemerintah, warga, dan swasta mengambil keputusan di satu meja — dengan data yang sama dan target yang sama.',
                'statistics' => [
                    ['value' => '6', 'label' => 'Policies'],
                    ['value' => '38', 'label' => 'Institutions in the forum'],
                    ['value' => '2018', 'label' => 'Running since'],
                ],
                'statistics_id' => [
                    ['value' => '6', 'label' => 'Kebijakan'],
                    ['value' => '38', 'label' => 'Lembaga di forum'],
                    ['value' => '2018', 'label' => 'Mulai berjalan'],
                ],
                'results' => [
                    [
                        'value' => '12k ha',
                        'title' => 'Area protected through forum decisions',
                        'description' => 'Designated after boundaries were agreed with communities and permit holders.',
                        'source' => 'Source: Regent Decree 2024 · updated Mar 2026',
                    ],
                    [
                        'value' => '6',
                        'title' => 'Derived regional policies',
                        'description' => 'Including a district forum and a requirement to use one land data source.',
                        'source' => 'Source: LTKL Secretariat 2025',
                    ],
                ],
                'results_id' => [
                    [
                        'value' => '12rb ha',
                        'title' => 'Kawasan terlindungi lewat keputusan forum',
                        'description' => 'Ditetapkan setelah batasnya disepakati bersama masyarakat dan pemegang izin.',
                        'source' => 'Sumber: SK Bupati 2024 · diperbarui Mar 2026',
                    ],
                    [
                        'value' => '6',
                        'title' => 'Kebijakan daerah turunan',
                        'description' => 'Termasuk pembentukan forum kabupaten dan kewajiban memakai satu sumber data lahan.',
                        'source' => 'Sumber: Sekretariat LTKL 2025',
                    ],
                ],
                'practices' => [
                    [
                        'since_year' => 2019,
                        'title' => 'A district forum that meets every quarter',
                        'title_id' => 'Forum kabupaten yang bertemu tiap kuartal',
                        'description' => 'Eight agencies, two cooperatives and village representatives draw up an annual plan from one map.',
                        'description_id' => 'Delapan lembaga daerah, dua koperasi, dan perwakilan desa menyusun rencana tahunan berbasis satu peta.',
                    ],
                    [
                        'since_year' => 2021,
                        'title' => 'One dataset, one map, one plan',
                        'title_id' => 'Satu data, satu peta, satu rencana',
                        'description' => 'Land cover data is public, so planning no longer rests on a single party\'s claim.',
                        'description_id' => 'Data tutupan lahan dibuka ke publik sehingga perencanaan tidak lagi bergantung pada klaim sepihak.',
                    ],
                ],
            ],
            [
                'title' => 'Community-led sustainable economy',
                'title_id' => 'Ekonomi lestari warga',
                'slug' => Str::slug('Community-led sustainable economy'),
                'slug_id' => Str::slug('Ekonomi lestari warga'),
                'technical_term' => 'Community-based sustainable commodities',
                'technical_term_id' => 'Komoditas lestari berbasis warga',
                'description' => 'Farmer income rises without opening new land, because buyers pay for provenance rather than volume alone.',
                'description_id' => 'Pendapatan petani naik tanpa membuka lahan baru, karena pembeli membayar asal-usul produk, bukan sekadar volume.',
                'statistics' => [
                    ['value' => '840', 'label' => 'Farming families'],
                    ['value' => '12', 'label' => 'Commodities mapped'],
                    ['value' => '2019', 'label' => 'Running since'],
                ],
                'statistics_id' => [
                    ['value' => '840', 'label' => 'Keluarga petani'],
                    ['value' => '12', 'label' => 'Komoditas terpetakan'],
                    ['value' => '2019', 'label' => 'Mulai berjalan'],
                ],
                'results' => [
                    [
                        'value' => '840',
                        'title' => 'Farming families in the sustainable commodity scheme',
                        'description' => 'Connected to buyers that require deforestation-free provenance.',
                        'source' => 'Source: Partner report 2025 · updated Jan 2026',
                    ],
                ],
                'results_id' => [
                    [
                        'value' => '840',
                        'title' => 'Keluarga petani masuk skema komoditas lestari',
                        'description' => 'Terhubung ke pembeli yang mensyaratkan asal produk tanpa deforestasi.',
                        'source' => 'Sumber: Laporan mitra 2025 · diperbarui Jan 2026',
                    ],
                ],
                'practices' => [
                    [
                        'since_year' => 2020,
                        'title' => 'Peat pineapple absorbed by local processors',
                        'title_id' => 'Nanas gambut diserap industri olahan lokal',
                        'description' => 'Grown without clearing new land, then processed within the district.',
                        'description_id' => 'Ditanam tanpa membuka lahan baru, lalu diolah di dalam kabupaten.',
                    ],
                ],
            ],
            [
                'title' => 'Youth leadership',
                'title_id' => 'Kepemimpinan muda',
                'slug' => Str::slug('Youth leadership'),
                'slug_id' => Str::slug('Kepemimpinan muda'),
                'technical_term' => 'Intergenerational leadership pipeline',
                'technical_term_id' => 'Regenerasi kepemimpinan lintas generasi',
                'description' => 'Young people from the districts run the programmes they will inherit, instead of being consulted about them.',
                'description_id' => 'Anak muda dari kabupaten menjalankan program yang akan mereka warisi, bukan sekadar dimintai pendapat.',
                'statistics' => [
                    ['value' => '210', 'label' => 'Young people involved'],
                    ['value' => '17', 'label' => 'Village initiatives'],
                    ['value' => '2022', 'label' => 'Running since'],
                ],
                'statistics_id' => [
                    ['value' => '210', 'label' => 'Anak muda terlibat'],
                    ['value' => '17', 'label' => 'Inisiatif desa'],
                    ['value' => '2022', 'label' => 'Mulai berjalan'],
                ],
                'results' => [
                    [
                        'value' => '17',
                        'title' => 'Village initiatives led by young people',
                        'description' => 'Each one budgeted and reported by the group that proposed it.',
                        'source' => 'Source: LTKL Secretariat 2025',
                    ],
                ],
                'results_id' => [
                    [
                        'value' => '17',
                        'title' => 'Inisiatif desa yang dipimpin anak muda',
                        'description' => 'Setiap inisiatif dianggarkan dan dilaporkan oleh kelompok pengusulnya.',
                        'source' => 'Sumber: Sekretariat LTKL 2025',
                    ],
                ],
                'practices' => [
                    [
                        'since_year' => 2022,
                        'title' => 'A youth cohort running the district data desk',
                        'title_id' => 'Kelompok muda yang menjalankan meja data kabupaten',
                        'description' => 'They keep the shared dataset current and answer requests from village heads.',
                        'description_id' => 'Mereka menjaga data bersama tetap mutakhir dan melayani permintaan kepala desa.',
                    ],
                ],
            ],
        ];
    }
}
