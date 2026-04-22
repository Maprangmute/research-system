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
    public const ROLE_USER_ALL = 'user_all';
    public const ROLE_RESEARCH_EXECUTIVE = 'research_executive';
    public const ROLE_RESIGNED = 'resigned';
    public const ROLE_UNIT_EXECUTIVE = 'unit_executive';

    public const MANAGERIAL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_USER_ALL,
        self::ROLE_RESEARCH_EXECUTIVE,
        self::ROLE_UNIT_EXECUTIVE,
    ];

    public const ADMIN_EQUIVALENT_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_UNIT_EXECUTIVE,
    ];

    public const USER_MANAGEMENT_ROLES = [
        self::ROLE_ADMIN,
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
}
