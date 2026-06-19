<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FteRole;

class FteRolePolicy
{
    /**
     * ทีมวิจัยแก้ได้: ถ้าเป็นเจ้าของระเบียน หรือเป็น admin/user_all
     */
    public function updateResearch(User $user, FteRole $fteRole): bool
    {
        if ($user->mustLimitFteToSelf()) {
            return $this->belongsToUser($user, $fteRole);
        }

        if ($this->isAdminLike($user)) return true;

        return $this->belongsToUser($user, $fteRole);
    }

    /**
     * ผู้บริหาร = admin หรือ user_all
     */
    public function updateManagement(User $user, FteRole $fteRole): bool
    {
        if ($user->mustLimitFteToSelf()) {
            return $this->belongsToUser($user, $fteRole);
        }

        return $this->isAdminLike($user);
    }

    /**
     * ✅ helper เช็กสิทธิ์แบบ admin
     */
    private function isAdminLike(User $user): bool
    {
        return $user->isAdminLike();
    }

    private function belongsToUser(User $user, FteRole $fteRole): bool
    {
        if (isset($fteRole->user_id)) {
            return (int)$fteRole->user_id === (int)$user->id;
        }

        $fullName = trim(($user->prefix ?? '').' '.$user->name);
        return trim((string) $fteRole->name) === $fullName;
    }
}
