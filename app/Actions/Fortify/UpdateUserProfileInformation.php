<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Support\UserDisplayNameSync;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        $oldPrefix = (string) ($user->prefix ?? '');
        $oldName = (string) ($user->name ?? '');

        Validator::make($input, [
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:4096'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        $name = isset($input['name']) && $input['name'] !== '' ? $input['name'] : $user->name;

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input, $name);
        } else {
            $user->forceFill([
                'name' => $name,
                'email' => $input['email'],
            ])->save();
        }

        UserDisplayNameSync::syncAfterResponse(
            $oldPrefix,
            $oldName,
            (string) ($user->prefix ?? ''),
            (string) ($user->name ?? ''),
            $user->id
        );
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input, string $name): void
    {
        $user->forceFill([
            'name' => $name,
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
