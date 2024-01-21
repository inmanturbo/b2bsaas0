<?php

namespace App\Models;

use App\WithUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Inmanturbo\B2bSaas\UsesLandlordConnection;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\TeamInvitation as JetstreamTeamInvitation;

class TeamInvitation extends JetstreamTeamInvitation
{
    use UsesLandlordConnection;
    use WithUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the team that the invitation belongs to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Jetstream::teamModel());
    }
}
