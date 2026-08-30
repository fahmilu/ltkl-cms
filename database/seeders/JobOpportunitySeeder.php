<?php

namespace Database\Seeders;

use App\Enums\EmploymentType;
use App\Enums\JobStatus;
use App\Models\JobOpportunity;
use Illuminate\Database\Seeder;

/**
 * Sample vacancies, shaped after the ones on kabupatenlestari.org/tentang-ltkl/karir.
 *
 * Rows are matched on their English slug, so running this again refreshes the
 * sample rather than duplicating it.
 */
class JobOpportunitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->vacancies() as $index => $vacancy) {
            JobOpportunity::updateOrCreate(
                ['slug' => $vacancy['slug']],
                $vacancy + [
                    'sorted_at' => $index + 1,
                    'contact_email' => 'recruitment@kabupatenlestari.org',
                    'apply_url' => 'https://kabupatenlestari.org/tentang-ltkl/karir/',
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function vacancies(): array
    {
        return [
            [
                'is_active' => true,
                'status' => JobStatus::OPEN,
                'employment_type' => EmploymentType::CONSULTANT,
                'title' => 'Consultant - Knowledge Management',
                'title_id' => 'Konsultan - Knowledge Management',
                'slug' => 'consultant-knowledge-management',
                'slug_id' => 'konsultan-knowledge-management',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>The Secretariat is looking for a consultant to organise the knowledge produced across the nine member districts into something the collective can actually reuse.</p><ul><li>Map the reports, datasets and field notes held by the Secretariat.</li><li>Design a knowledge base the district teams can maintain themselves.</li><li>Run two writing clinics with district focal points.</li></ul>',
                'description_id' => '<p>Sekretariat mencari konsultan untuk merapikan pengetahuan dari sembilan kabupaten anggota menjadi sesuatu yang bisa dipakai ulang oleh kolektif.</p><ul><li>Memetakan laporan, data, dan catatan lapangan yang dimiliki Sekretariat.</li><li>Merancang basis pengetahuan yang bisa dirawat sendiri oleh tim kabupaten.</li><li>Menjalankan dua klinik penulisan bersama focal point kabupaten.</li></ul>',
                'how_to_apply' => '<p>Send your CV, a short portfolio and your daily rate to <strong>recruitment@kabupatenlestari.org</strong> with the subject <em>Knowledge Management</em>. The recruitment process at LTKL is free of charge.</p>',
                'how_to_apply_id' => '<p>Kirim CV, portofolio singkat, dan tarif harian Anda ke <strong>recruitment@kabupatenlestari.org</strong> dengan subjek <em>Knowledge Management</em>. Proses rekrutmen di LTKL tidak dipungut biaya.</p>',
                'posted_at' => '2026-08-04',
                'deadline_at' => '2026-09-19',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::OPEN,
                'employment_type' => EmploymentType::FULL_TIME,
                'title' => 'Business & Portfolio Development Coordinator',
                'title_id' => 'Koordinator Pengembangan Bisnis & Portofolio',
                'slug' => 'business-portfolio-development-coordinator',
                'slug_id' => 'koordinator-pengembangan-bisnis-portofolio',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Build the pipeline of investable, nature-based businesses coming out of the member districts, and keep the portfolio moving from idea to deal.</p><ul><li>Screen and coach district enterprises towards investment readiness.</li><li>Hold the relationship with impact investors and blended finance partners.</li><li>Report portfolio progress to the Secretariat every quarter.</li></ul>',
                'description_id' => '<p>Membangun pipeline usaha berbasis alam yang layak investasi dari kabupaten anggota, dan menjaga portofolio bergerak dari gagasan menjadi kesepakatan.</p><ul><li>Menyeleksi dan mendampingi usaha kabupaten menuju kesiapan investasi.</li><li>Menjaga hubungan dengan investor dampak dan mitra pembiayaan campuran.</li><li>Melaporkan perkembangan portofolio ke Sekretariat setiap triwulan.</li></ul>',
                'how_to_apply' => '<p>Apply through the career page with your CV and a one-page note on a deal you helped close.</p>',
                'how_to_apply_id' => '<p>Lamar melalui halaman karier dengan CV dan catatan satu halaman tentang satu kesepakatan yang pernah Anda bantu rampungkan.</p>',
                'posted_at' => '2026-08-11',
                'deadline_at' => '2026-09-30',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::OPEN,
                'employment_type' => EmploymentType::FULL_TIME,
                'title' => 'Strategic Communication Coordinator',
                'title_id' => 'Koordinator Komunikasi Strategis',
                'slug' => 'strategic-communication-coordinator',
                'slug_id' => 'koordinator-komunikasi-strategis',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Tell the story of the collective in a way that holds up in front of a regent, a buyer and a journalist on the same day.</p><ul><li>Own the editorial calendar across the website and social channels.</li><li>Turn district results into briefs, decks and press material.</li><li>Coach district communication officers.</li></ul>',
                'description_id' => '<p>Menceritakan kerja kolektif dengan cara yang tetap kuat di hadapan bupati, pembeli, maupun jurnalis pada hari yang sama.</p><ul><li>Memegang kalender editorial di situs web dan kanal media sosial.</li><li>Mengolah capaian kabupaten menjadi brief, dek presentasi, dan bahan media.</li><li>Mendampingi staf komunikasi kabupaten.</li></ul>',
                'how_to_apply' => '<p>Send your CV and two writing samples in Bahasa Indonesia and English.</p>',
                'how_to_apply_id' => '<p>Kirim CV dan dua contoh tulisan dalam Bahasa Indonesia dan Bahasa Inggris.</p>',
                'posted_at' => '2026-08-18',
                'deadline_at' => '2026-10-10',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::OPEN,
                'employment_type' => EmploymentType::CONTRACT,
                'title' => 'Project Officer - Festival Lestari Kapuas Hulu',
                'title_id' => 'Project Officer - Festival Lestari Kapuas Hulu',
                'slug' => 'project-officer-festival-lestari-kapuas-hulu',
                'slug_id' => 'project-officer-festival-lestari-kapuas-hulu-id',
                'location' => 'Kapuas Hulu, West Kalimantan',
                'location_id' => 'Kapuas Hulu, Kalimantan Barat',
                'description' => '<p>Run the day to day of Festival Lestari in Kapuas Hulu, from the first coordination meeting to the closing report.</p><ul><li>Coordinate vendors, villages and the district government.</li><li>Keep the budget and the activity plan in step.</li><li>Document the festival for the collective\'s reporting.</li></ul>',
                'description_id' => '<p>Menjalankan operasional harian Festival Lestari di Kapuas Hulu, dari rapat koordinasi pertama sampai laporan penutup.</p><ul><li>Mengoordinasikan vendor, desa, dan pemerintah kabupaten.</li><li>Menjaga anggaran dan rencana kegiatan tetap sejalan.</li><li>Mendokumentasikan festival untuk pelaporan kolektif.</li></ul>',
                'how_to_apply' => '<p>Candidates based in or willing to relocate to Kapuas Hulu are prioritised. Send your CV to the contact email.</p>',
                'how_to_apply_id' => '<p>Kandidat yang berdomisili atau bersedia pindah ke Kapuas Hulu diprioritaskan. Kirim CV Anda ke email kontak.</p>',
                'posted_at' => '2026-08-25',
                'deadline_at' => '2026-11-15',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::FULL_TIME,
                'title' => 'Government Affair & Policy Advocacy Coordinator',
                'title_id' => 'Koordinator Hubungan Pemerintah & Advokasi Kebijakan',
                'slug' => 'government-affair-policy-advocacy-coordinator',
                'slug_id' => 'koordinator-hubungan-pemerintah-advokasi-kebijakan',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Hold the collective\'s relationship with national ministries and translate district practice into policy asks that can actually be signed.</p>',
                'description_id' => '<p>Menjaga hubungan kolektif dengan kementerian dan menerjemahkan praktik kabupaten menjadi usulan kebijakan yang benar-benar bisa ditandatangani.</p>',
                'how_to_apply' => '<p>This vacancy is closed. Applications are no longer reviewed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup. Lamaran tidak lagi ditinjau.</p>',
                'posted_at' => '2026-03-10',
                'deadline_at' => '2026-04-16',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::CONSULTANT,
                'title' => 'Consultant - Sustainable Investment Development',
                'title_id' => 'Konsultan - Sustainable Investment Development',
                'slug' => 'consultant-sustainable-investment-development',
                'slug_id' => 'konsultan-sustainable-investment-development',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Design the investment thesis for commodities produced without clearing new land, and test it against three district portfolios.</p>',
                'description_id' => '<p>Menyusun tesis investasi untuk komoditas yang diproduksi tanpa membuka lahan baru, lalu mengujinya pada tiga portofolio kabupaten.</p>',
                'how_to_apply' => '<p>This vacancy is closed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup.</p>',
                'posted_at' => '2026-02-09',
                'deadline_at' => '2026-03-16',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::CONSULTANT,
                'title' => 'Consultant - Program Specialist of Sustainable Business and Investment',
                'title_id' => 'Konsultan - Program Specialist of Sustainable Business and Investment',
                'slug' => 'consultant-program-specialist-sustainable-business-and-investment',
                'slug_id' => 'konsultan-program-specialist-sustainable-business-and-investment',
                'location' => 'Remote',
                'location_id' => 'Bekerja jarak jauh',
                'description' => '<p>Support the business and investment programme from wherever you are, with two field visits per quarter.</p>',
                'description_id' => '<p>Mendukung program bisnis dan investasi dari mana pun Anda berada, dengan dua kunjungan lapangan per triwulan.</p>',
                'how_to_apply' => '<p>This vacancy is closed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup.</p>',
                'posted_at' => '2026-02-09',
                'deadline_at' => '2026-03-16',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::CONSULTANT,
                'title' => 'Consultant - Sustainable Enterprise Readiness',
                'title_id' => 'Konsultan - Sustainable Enterprise Readiness',
                'slug' => 'consultant-sustainable-enterprise-readiness',
                'slug_id' => 'konsultan-sustainable-enterprise-readiness',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Assess how ready district enterprises are for outside capital, and write the readiness curriculum the Secretariat will run afterwards.</p>',
                'description_id' => '<p>Menilai kesiapan usaha kabupaten menerima modal dari luar, lalu menyusun kurikulum kesiapan yang akan dijalankan Sekretariat.</p>',
                'how_to_apply' => '<p>This vacancy is closed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup.</p>',
                'posted_at' => '2026-02-02',
                'deadline_at' => '2026-03-16',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::FULL_TIME,
                'title' => 'Awards Management Coordinator',
                'title_id' => 'Koordinator Manajemen Penghargaan',
                'slug' => 'awards-management-coordinator',
                'slug_id' => 'koordinator-manajemen-penghargaan',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Run the collective\'s awards cycle end to end: call for entries, jury process, and the announcement at the annual gathering.</p>',
                'description_id' => '<p>Menjalankan siklus penghargaan kolektif dari awal sampai akhir: pengumpulan usulan, proses juri, hingga pengumuman di pertemuan tahunan.</p>',
                'how_to_apply' => '<p>This vacancy is closed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup.</p>',
                'posted_at' => '2026-01-19',
                'deadline_at' => '2026-02-28',
            ],
            [
                'is_active' => true,
                'status' => JobStatus::CLOSED,
                'employment_type' => EmploymentType::CONTRACT,
                'title' => 'Project Manager for Organizational Development',
                'title_id' => 'Manajer Proyek Pengembangan Organisasi',
                'slug' => 'project-manager-for-organizational-development',
                'slug_id' => 'manajer-proyek-pengembangan-organisasi',
                'location' => 'Jakarta, Indonesia',
                'location_id' => 'Jakarta, Indonesia',
                'description' => '<p>Lead the Secretariat through its next organisational step: roles, standard operating procedures, and the systems that hold them.</p>',
                'description_id' => '<p>Memimpin Sekretariat melewati tahap pengembangan organisasi berikutnya: peran, prosedur operasi standar, dan sistem yang menopangnya.</p>',
                'how_to_apply' => '<p>This vacancy is closed.</p>',
                'how_to_apply_id' => '<p>Lowongan ini telah ditutup.</p>',
                'posted_at' => '2026-01-19',
                'deadline_at' => '2026-02-28',
            ],
        ];
    }
}
