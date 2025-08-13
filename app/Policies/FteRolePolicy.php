<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FteRole;

class FteRolePolicy
{
    /**
     * ทีมวิจัยแก้ได้: ถ้าเป็นเจ้าของระเบียน หรือเป็นแอดมิน
     * - ปรับตามโครงจริงของคุณ:
     *   - ถ้ามีคอลัมน์ user_id ใช้เทียบกับ $user->id
     *   - ถ้าไม่มี ให้เทียบชื่อเต็มแทน (prefix + name)
     */
    public function updateResearch(User $user, FteRole $fteRole): bool
    {
        if ($this->isAdmin($user)) return true;

        if (isset($fteRole->user_id)) {
            return (int)$fteRole->user_id === (int)$user->id;
        }

        $fullName = trim(($user->prefix ?? '').' '.$user->name);
        return $fteRole->name === $fullName;
    }

    /**
     * ผู้บริหาร = admin เท่านั้น
     */
    public function updateManagement(User $user, FteRole $fteRole): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        // ปรับตามระบบของคุณ:
        // - ถ้ามี is_admin (boolean)
        if (property_exists($user, 'is_admin') || isset($user->is_admin)) {
            return (bool)$user->is_admin;
        }
        // - หรือถ้ามี role = 'admin'
        if (property_exists($user, 'role') || isset($user->role)) {
            return $user->role === 'admin';
        }
        return false;
    }
}
