<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SU_ADMIN = 'su_admin';
    public const ROLE_USER_ALL = 'user_all';
    public const ROLE_RESEARCH_EXECUTIVE = 'research_executive';
    public const ROLE_RESIGNED = 'resigned';
    public const ROLE_UNIT_EXECUTIVE = 'unit_executive';

    public const SKILL_ROLE_SCIENTIFIC = 'Scientific Research';
    public const SKILL_ROLE_PROJECT_MANAGEMENT = 'Project Management & Coordination';
    public const SKILL_ROLE_IT_DATA = 'IT & Data Management Systems';
    public const SKILL_ROLE_COMMUNICATION = 'Communication & Public Relations';
    public const SKILL_ROLE_OPERATIONS = 'Operations & Administrative Support';
    public const SKILL_ROLE_GENERAL = 'General';
    public const SKILL_ROLE_UNSPECIFIED = 'ไม่ระบุสายงาน';

    public const SKILL_ROLES = [
        self::SKILL_ROLE_SCIENTIFIC,
        self::SKILL_ROLE_PROJECT_MANAGEMENT,
        self::SKILL_ROLE_IT_DATA,
        self::SKILL_ROLE_COMMUNICATION,
        self::SKILL_ROLE_OPERATIONS,
        self::SKILL_ROLE_GENERAL,
        self::SKILL_ROLE_UNSPECIFIED,
    ];

    public const MANAGERIAL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SU_ADMIN,
        self::ROLE_USER_ALL,
        self::ROLE_RESEARCH_EXECUTIVE,
        self::ROLE_UNIT_EXECUTIVE,
    ];

    public const ADMIN_EQUIVALENT_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SU_ADMIN,
        self::ROLE_UNIT_EXECUTIVE,
    ];

    public const USER_MANAGEMENT_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SU_ADMIN,
        self::ROLE_USER_ALL,
        self::ROLE_RESEARCH_EXECUTIVE,
    ];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
  protected $fillable = [
    'name',
    'prefix_en',
    'name_en',
    'email',
    'password',
    'prefix',
    'position',
    'skill_role',
    'role',
    'profile_photo_path',
];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        /**
     * ตรวจสอบว่า user มี role ตามที่กำหนดหรือไม่
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * ตรวจสอบว่า user มีสิทธิ์แบบ admin หรือไม่
     * (รวมทั้ง role = 'admin' และ 'user_all')
     */
    public function isAdminLike(): bool
    {
        return in_array($this->role, self::MANAGERIAL_ROLES, true);
    }

    public function isAdminEquivalent(): bool
    {
        return in_array($this->role, self::ADMIN_EQUIVALENT_ROLES, true);
    }

    public function isAllUserLike(): bool
    {
        return in_array($this->role, [
            self::ROLE_USER_ALL,
            self::ROLE_RESEARCH_EXECUTIVE,
        ], true);
    }

    public function isUnitExecutive(): bool
    {
        return $this->role === self::ROLE_UNIT_EXECUTIVE;
    }

    public function isResigned(): bool
    {
        return $this->role === self::ROLE_RESIGNED;
    }

    public function canAccessUserManagement(): bool
    {
        return in_array($this->role, self::USER_MANAGEMENT_ROLES, true);
    }

    public function getFullNameAttribute(): string
    {
        return trim(((string) $this->prefix) . ' ' . ((string) $this->name));
    }

    public function getResearchDisplayNameAttribute(): string
    {
        $englishName = trim(((string) ($this->prefix_en ?? '')) . ' ' . ((string) ($this->name_en ?? '')));

        return $englishName !== '' ? $englishName : $this->full_name;
    }

    public static function inferSkillRoleFromPosition(?string $position, ?string $name = null): string
    {
        $text = mb_strtolower(trim(((string) $name) . ' ' . ((string) $position)), 'UTF-8');
        if ($text === '') {
            return self::SKILL_ROLE_UNSPECIFIED;
        }

        if (str_contains($text, 'หัวหน้าหน่วย') || str_contains($text, 'รองหัวหน้าหน่วย') || str_contains($text, 'health policy') || str_contains($text, 'policy unit')) {
            return self::SKILL_ROLE_SCIENTIFIC;
        }

        if (str_contains($text, 'ประชาสัมพันธ์') || str_contains($text, 'communication') || str_contains($text, 'สื่อสาร') || str_contains($text, 'ผลิตสื่อ') || str_contains($text, 'ออกแบบ') || str_contains($text, 'branding')) {
            return self::SKILL_ROLE_COMMUNICATION;
        }

        if (str_contains($text, 'สารสนเทศ') || str_contains($text, 'นักวิเคราะห์ข้อมูล') || str_contains($text, 'data') || str_contains($text, 'สถิติ') || str_contains($text, 'วิเคราะห์ข้อมูล') || str_contains($text, 'developer') || str_contains($text, 'engineer') || str_contains($text, 'เทคโนโลยีสารสนเทศ')) {
            return self::SKILL_ROLE_IT_DATA;
        }

        if (str_contains($text, 'นักบริหารงานวิจัย') || str_contains($text, 'บริหารงานวิจัย') || str_contains($text, 'project manager') || str_contains($text, 'research administrator') || str_contains($text, 'research management')) {
            return self::SKILL_ROLE_PROJECT_MANAGEMENT;
        }

        if (str_contains($text, 'พนักงานทั่วไป')) {
            return self::SKILL_ROLE_GENERAL;
        }

        if (str_contains($text, 'บริหารงานทั่วไป') || str_contains($text, 'ธุรการ') || str_contains($text, 'administration') || str_contains($text, 'admin') || str_contains($text, 'เลขานุการ')) {
            return self::SKILL_ROLE_OPERATIONS;
        }

        if (
            str_contains($text, 'นักวิจัย') ||
            str_contains($text, 'ผู้ช่วยวิจัย') ||
            str_contains($text, 'เจ้าหน้าที่วิจัย') ||
            str_contains($text, 'แพทย์') ||
            str_contains($text, 'อาจารย์แพทย์') ||
            str_contains($text, 'พยาบาล') ||
            str_contains($text, 'เภสัช') ||
            str_contains($text, 'นักจิตวิทยา') ||
            str_contains($text, 'นักโภชนาการ') ||
            str_contains($text, 'นักสุขศึกษา') ||
            str_contains($text, 'research consultant') ||
            str_contains($text, 'consultant') ||
            str_contains($text, 'ที่ปรึกษา') ||
            str_contains($text, 'คณบดี')
        ) {
            return self::SKILL_ROLE_SCIENTIFIC;
        }

        return self::SKILL_ROLE_GENERAL;
    }

    public static function inferFtePositionGroup(?string $position, ?string $name = null): string
    {
        $text = mb_strtolower(trim(((string) $name) . ' ' . ((string) $position)), 'UTF-8');
        if ($text === '') {
            return 'ไม่ระบุตำแหน่ง';
        }

        if (str_contains($text, 'แพทย์ใช้ทุน') || str_contains($text, 'intern')) {
            return 'แพทย์ Intern';
        }

        if (str_contains($text, 'อาจารย์แพทย์') || str_contains($text, 'แพทย์') || str_contains($text, 'คณบดี')) {
            return 'แพทย์';
        }

        if (str_contains($text, 'นักวิชาการสารสนเทศ')) {
            return 'นักวิชาการสารสนเทศ';
        }

        if (str_contains($text, 'นักวิชาการสถิติ') || str_contains($text, 'นักวิเคราะห์ข้อมูล')) {
            return 'นักสถิติ/นักวิเคราะห์ข้อมูล';
        }

        if (str_contains($text, 'นักประชาสัมพันธ์')) {
            return 'นักประชาสัมพันธ์';
        }

        if (str_contains($text, 'เจ้าหน้าที่บริหารงานทั่วไป') || str_contains($text, 'พนักงานทั่วไป')) {
            return 'เจ้าหน้าที่บริหารงานทั่วไป';
        }

        if (str_contains($text, 'นักบริหารงานวิจัย')) {
            return 'บริหารงานวิจัย';
        }

        if (str_contains($text, 'เจ้าหน้าที่วิจัย')) {
            return 'เจ้าหน้าที่วิจัย';
        }

        if (str_contains($text, 'ผู้ช่วยวิจัย')) {
            return 'ผู้ช่วยวิจัย';
        }

        if (str_contains($text, 'นักวิจัย') || str_contains($text, 'research consultant')) {
            return 'นักวิจัย';
        }

        if (
            str_contains($text, 'พยาบาล') ||
            str_contains($text, 'เภสัช') ||
            str_contains($text, 'นักจิตวิทยา') ||
            str_contains($text, 'นักโภชนาการ') ||
            str_contains($text, 'นักสุขศึกษา')
        ) {
            return 'สหสาขาวิชาชีพ';
        }

        if (str_contains($text, 'หัวหน้าหน่วย') || str_contains($text, 'รองหัวหน้าหน่วย') || str_contains($text, 'health policy') || str_contains($text, 'policy unit')) {
            return 'นักวิจัย';
        }

        return trim((string) $position) !== '' ? trim((string) $position) : 'ไม่ระบุตำแหน่ง';
    }
}
