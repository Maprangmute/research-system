<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FillUserEnglishNameCandidates extends Command
{
    protected $signature = 'users:fill-name-en-candidates
        {--dry-run : Preview candidate fills without writing to the database}';

    protected $description = 'Fill users.name_en from a conservative curated Thai-to-English candidate map';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $candidateMap = $this->candidateMap();

        if ($candidateMap === []) {
            $this->warn('No candidate mappings configured.');
            return self::FAILURE;
        }

        $users = User::query()
            ->select(['id', 'prefix', 'name', 'name_en'])
            ->where(function ($query) {
                $query->whereNull('name_en')
                    ->orWhereRaw("TRIM(name_en) = ''");
            })
            ->orderBy('id')
            ->get();

        $usersByNormalizedName = [];
        foreach ($users as $user) {
            $key = $this->normalizeThaiKey((string) $user->name);
            if ($key === '') {
                continue;
            }

            $usersByNormalizedName[$key] ??= [];
            $usersByNormalizedName[$key][] = $user;
        }

        $stats = [
            'mapped' => 0,
            'updated' => 0,
            'ambiguous' => 0,
            'missing' => 0,
        ];

        $rows = [];

        foreach ($candidateMap as $thaiName => $englishName) {
            $normalizedThaiName = $this->normalizeThaiKey($thaiName);
            $matchedUsers = $usersByNormalizedName[$normalizedThaiName] ?? [];

            if ($matchedUsers === []) {
                $stats['missing']++;
                $rows[] = [$thaiName, $englishName, '-', 'missing'];
                continue;
            }

            if (count($matchedUsers) > 1) {
                $stats['ambiguous']++;
                $rows[] = [$thaiName, $englishName, implode(', ', array_map(fn (User $user) => (string) $user->id, $matchedUsers)), 'ambiguous'];
                continue;
            }

            /** @var User $user */
            $user = $matchedUsers[0];
            $stats['mapped']++;

            if (! $dryRun) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('name_en')
                            ->orWhereRaw("TRIM(name_en) = ''");
                    })
                    ->update([
                        'name_en' => $englishName,
                        'updated_at' => now(),
                    ]);

                $stats['updated']++;
            }

            $rows[] = [$thaiName, $englishName, (string) $user->id, $dryRun ? 'would update' : 'updated'];
        }

        $this->info($dryRun ? 'Dry run mode: no users.name_en values will be written.' : 'Writing users.name_en from curated candidate map...');
        $this->table(['Thai Name', 'English Name', 'User ID', 'Status'], $rows);
        $this->newLine();
        $this->info("mapped={$stats['mapped']} updated={$stats['updated']} ambiguous={$stats['ambiguous']} missing={$stats['missing']}");

        return self::SUCCESS;
    }

    /**
     * @return array<string, string>
     */
    private function candidateMap(): array
    {
        return [
            'วราลักษณ์ ศรีนนท์ประเสริฐ' => 'Varalak Srinonprasert',
            'พัทธรา ลีฬหวรงค์' => 'Pattara Leelahavarong',
            'รุ่งนภา คำผาง' => 'Roongnapa Khampang',
            'จุฑามาศ พราวแจ้ง' => 'Juthamas Prawjaeng',
            'ศักดิโชติ เพชรสม' => 'Sakdichod Petsom',
            'นริสา ตัณฑัยย์' => 'Narisa Tantai',
            'ศตนันทน์ มณีอ่อน' => 'Satanun Maneeon',
            'รินรดา ปรีดาจิตกุล' => 'Rinrada Preedachitkul',
            'สุภาวดี ไทรนิ่มนวล' => 'Supawadee Sainimnuan',
            'ณัฐกานต์ จิตพิมพ์' => 'Natthakan Chitpim',
            'ธนวรรณ คงมาลัย' => 'Thanawan Kongmalai',
            'ปทุมพร สุรอรุณสัมฤทธิ์' => 'Patumporn Sura-arunsumrit',
            'พรธิดา หัดโนนตุ่น' => 'Phorntida Hadnorntun',
        ];
    }

    private function normalizeThaiKey(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return mb_strtolower($value, 'UTF-8');
    }
}
