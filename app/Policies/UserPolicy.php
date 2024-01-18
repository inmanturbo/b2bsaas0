<?php

namespace App\Policies;

use App\Models\SuperAdmin;
use App\Models\User;
use App\UserType;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /** The types that can perform impersonation */
    protected function impersonatorTypes(): array
    {
        return [
            UserType::SuperAdmin->name,
        ];
    }

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User|SuperAdmin $user, User|SuperAdmin $model)
    {
        if ($model->type === UserType::SuperAdmin->name && $user->type !== UserType::SuperAdmin->name) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateUserType(User $user)
    {
        if ($user->isImpersonated()) {
            $impersonator = User::find(session('impersonator'));

            return $impersonator->type === UserType::SuperAdmin->name;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $model
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function updateTeam(User $user)
    {
        return ! $user->isImpersonated();
    }

    public function impersonate(User|SuperAdmin $user, User|SuperAdmin $model)
    {

        if (! $this->impersonateAny($user)) {
            return false;
        }

        if (! in_array($model->email, config('b2bsaas.impersonation.disallowed_emails', []))) {

            return true;
        }

        return false;
    }

    protected function impersonateAny(User|SuperAdmin $user)
    {
        return in_array($user->type, $this->impersonatorTypes());
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, User $model)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, User $model)
    {
        //
    }
}
