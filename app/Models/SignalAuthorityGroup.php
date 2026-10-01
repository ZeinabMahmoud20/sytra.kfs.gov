<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SignalAuthorityGroup extends Model
{
    protected $table = 'SIGNAL_AUTHORITY_GROUP';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'GROUP_NAME',
    ];

    /**
     * جهات الإشارة اللي عضو في الجروب ده
     */
    public function authorities(): BelongsToMany
    {
        return $this->belongsToMany(
            SignalAuthority::class,
            'SIGNAL_AUTHORITY_GROUP_MEMBER',
            'GROUP_ID',
            'AUTHORITY_ID'
        )->orderBy('SIGNAL_NAME');
    }
}
