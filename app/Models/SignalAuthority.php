<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SignalAuthority extends Model
{
    protected $table = 'SIGNAL_AUTHORITY';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SIGNAL_NAME',
    ];

    /**
     * المجموعات (جروبات) اللي الجهة دي عضو فيها
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            SignalAuthorityGroup::class,
            'SIGNAL_AUTHORITY_GROUP_MEMBER',
            'AUTHORITY_ID',
            'GROUP_ID'
        );
    }
}