<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invite extends Model
{
    use HasFactory;

    protected $fillable = array('email', 'token');
    /**
     * Get the temp associated with the Invite
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function temp()
    {
        return $this->hasOne(Temp::class);
    }
}
