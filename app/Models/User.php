<?php

namespace App\Models;

use App\UserType;
use App\WithUuid;
use B2bSaas\HasChildren;
use B2bSaas\TeamDatabase;
use B2bSaas\UsesLandlordConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasChildren;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams {
        switchTeam as jetstreamSwitchTeam;
    }
    use Notifiable;
    use TwoFactorAuthenticatable;
    use UsesLandlordConnection;
    use WithUuid;

    protected $guarded = [];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getChildTypes()
    {
        return UserType::toArray();
    }

    public function teamDatabases()
    {
        return $this->hasMany(TeamDatabase::class);
    }

    /**
     * Switch the user's context to the given team.
     *
     * @param  mixed  $team
     */
    public function switchTeam($team): bool
    {
        $isOnTeam = $this->jetstreamSwitchTeam($team);

        $team?->configure()?->use();

        return $isOnTeam;
    }
}
