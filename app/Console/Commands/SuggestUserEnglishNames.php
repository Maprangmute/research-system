<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SuggestUserEnglishNames extends Command
{
    protected $signature = 'outputs:suggest-user-english-names
        {--limit=100 : Limit the number of rows shown per section}';

    protected $description = 'Print candidate unmatched English names alongside users who still have no name_en';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $usersMissingEnglish = User::query()
            ->select(['id', 'prefix', 'name'])
            ->where(function ($query) {
                $query->whereNull('name_en')
                    ->orWhereRaw("TRIM(name_en) = ''");
            })
            ->orderBy('id')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'thai_name' => $user->full_name,
                ];
            });

        $unmatchedEnglishNames = $this->collectUnmatchedEnglishNames()
            ->sortByDesc('count')
            ->values();

        $this->info('Users without name_en');
        $this->table(
            ['User ID', 'Thai Name'],
            $usersMissingEnglish->take($limit)->map(fn ($row) => [$row['id'], $row['thai_name']])->all()
        );

        $this->newLine();
        $this->info('Unmatched English Names From Outputs');
        $this->table(
            ['English Name', 'Count', 'Sources'],
            $unmatchedEnglishNames->take($limit)->map(function ($row) {
                return [
                    $row['name'],
                    $row['count'],
                    implode(', ', $row['sources']),
                ];
            })->all()
        );

        $this->newLine();
        $this->line('Use this report to fill users.name_en manually, then rerun:');
        $this->line('php artisan outputs:backfill-user-ids --dry-run');
        $this->line('php artisan outputs:backfill-user-ids');

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, array{name:string,count:int,sources:array<int,string>}>
     */
    private function collectUnmatchedEnglishNames(): Collection
    {
        $rows = collect();

        foreach ([
            ['table' => 'output_authors', 'column' => 'name', 'label' => 'output_authors'],
            ['table' => 'external_output_authors', 'column' => 'name', 'label' => 'external_output_authors'],
            ['table' => 'output_book_authors', 'column' => 'name', 'label' => 'output_book_authors'],
        ] as $config) {
            $items = DB::table($config['table'])
                ->selectRaw("TRIM({$config['column']}) as raw_name, COUNT(*) as aggregate")
                ->whereNull('user_id')
                ->whereNotNull($config['column'])
                ->whereRaw("TRIM({$config['column']}) <> ''")
                ->groupBy('raw_name')
                ->get();

            foreach ($items as $item) {
                $name = trim((string) $item->raw_name);
                if (! $this->looksEnglish($name)) {
                    continue;
                }

                $rows->push([
                    'name' => $name,
                    'count' => (int) $item->aggregate,
                    'source' => $config['label'],
                ]);
            }
        }

        return $rows
            ->groupBy('name')
            ->map(function (Collection $group, string $name) {
                return [
                    'name' => $name,
                    'count' => $group->sum('count'),
                    'sources' => $group->pluck('source')->unique()->values()->all(),
                ];
            })
            ->values();
    }

    private function looksEnglish(string $value): bool
    {
        return (bool) preg_match('/[A-Za-z]/', $value);
    }
}
