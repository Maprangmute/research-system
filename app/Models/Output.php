<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProjectLog;


class Output extends Model
{
    protected $touches = ['project'];

    /**
     * ฟิลด์ที่กรอกผ่าน mass assignment
     */
    protected $fillable = [
        'project_id',
        'output_code',
        'type',            // EA101..EA120
        'title',           // ชื่อผลงาน
        'year',            // วันที่ publish (string/date-string)
        'project_output',  // รายละเอียด/หมายเหตุ/ลิงก์
        // EA101 only
        'journal',
        'quartile',        // Q1..Q4
        'impact_factor',   // decimal(5,3)
        // EA119 only
        'book_title',
        'publisher_name',
        'chapter_pages',
    ];


public function conferences()
{
    return $this->hasMany(OutputConference::class);
}


  // ความสัมพันธ์กับ OutputAuthor (ใหม่)
public function authors()
{
    return $this->hasMany(\App\Models\OutputAuthor::class, 'output_id', 'id');
}

public function bookAuthors()
{
    return $this->hasMany(\App\Models\OutputBookAuthor::class, 'output_id', 'id');
}


    /**
     * ค่า default
     */
    protected $attributes = [
        'journal'       => null,
        'quartile'      => null,
        'impact_factor' => null,
        'book_title'    => null,
        'publisher_name'=> null,
        'chapter_pages' => null,
    ];

    // ==== ความสัมพันธ์ ====
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ==== Scopes / Helpers ====
    public function scopeEA101($query)
    {
        return $query->where('type', 'like', 'EA101%');
    }

    

    

    public function isEA101(): bool
    {
        return is_string($this->type) && str_starts_with($this->type, 'EA101');
    }

    public static function formatOutputCode(int $id): string
    {
        return 'pv_' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ดึงรายชื่อผู้เขียนตามบทบาท (first|co|corresponding)
     * @return string[] รายชื่อ
     */
    public function authorsByRole(string $role): array
    {
        $role = strtolower(trim($role));
        return $this->authors()
            ->where('role', $role)
            ->pluck('name')
            ->all();
    }

    /**
     * แทนที่รายชื่อผู้เขียนทั้งหมดของ output นี้
     * @param array $authors [{role: 'first|co|corresponding', name: '...'}, ...]
     */
    public function syncAuthors(array $authors): void
    {
        // ทำความสะอาด
        $clean = array_values(array_filter(array_map(function ($a) {
            $role = isset($a['role']) ? strtolower(trim((string) $a['role'])) : null;
            $name = isset($a['name']) ? trim((string) $a['name']) : null;
            $userId = isset($a['user_id']) ? (int) $a['user_id'] : null;
            if (!$role || !$name) return null;
            if (!in_array($role, ['first','co','corresponding','awardee'], true)) return null;
            return ['role' => $role, 'name' => $name, 'user_id' => $userId > 0 ? $userId : null];
        }, $authors)));

        // ลบของเดิมแล้วเพิ่มใหม่ (มี unique(output_id,role,name) กันซ้ำใน DB)
        $this->authors()->delete();
        if (!empty($clean)) {
            $this->authors()->createMany($clean);
        }
    }

    // ==== Mutators ====

    /**
     * บังคับ quartile ให้เป็น Q1..Q4, รองรับกรอก "1","2"
     */
    public function setQuartileAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['quartile'] = null;
            return;
        }
        $v = strtoupper(trim((string)$value));
        if (in_array($v, ['Q1','Q2','Q3','Q4'], true)) {
            $this->attributes['quartile'] = $v;
            return;
        }
        if (in_array($v, ['1','2','3','4'], true)) {
            $this->attributes['quartile'] = 'Q' . $v;
            return;
        }
        $this->attributes['quartile'] = null;
    }

    // ==== Project Logs ====
    protected static function booted()
    {
        // Log create
        static::created(function ($output) {
            ProjectLog::create([
                'project_id'      => $output->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $output->project->project_name_th ?? '',
                'action'          => 'output_create',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($output->toArray()),
                    'output',
                    $output->id
                ),
            ]);

            if (empty($output->output_code) && $output->id) {
                $output->forceFill([
                    'output_code' => self::formatOutputCode((int) $output->id),
                ])->saveQuietly();
            }
        });

        // Log update (เฉพาะฟิลด์ที่เปลี่ยน)
        static::updating(function ($output) {
            $changes = ProjectLog::buildUpdateChanges($output);
            if (!empty($changes)) {
                ProjectLog::create([
                    'project_id'      => $output->project_id,
                    'user_id'         => Auth::id() ?? null,
                    'project_name_th' => $output->project->project_name_th ?? '',
                    'action'          => 'output_update',
                    'changes'         => ProjectLog::withRecordContext($changes, 'output', $output->id),
                ]);
            }
        });

        // Log delete
        static::deleting(function ($output) {
            // FK onDelete('cascade') จะลบ authors ให้อัตโนมัติ
            ProjectLog::create([
                'project_id'      => $output->project_id,
                'user_id'         => Auth::id() ?? null,
                'project_name_th' => $output->project->project_name_th ?? '',
                'action'          => 'output_delete',
                'changes'         => ProjectLog::withRecordContext(
                    ProjectLog::sanitizeChanges($output->toArray()),
                    'output',
                    $output->id
                ),
            ]);
        });
    }
}
