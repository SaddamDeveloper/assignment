<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temp extends Model
{
    use HasFactory;

    protected $fillable = array('invite_id', 'code_no', 'user_name', 'password');

    /**
     * Get the invites that owns th
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invite()
    {
        return $this->belongsTo(Invite::class, 'invite_id', 'id');
    }
}
