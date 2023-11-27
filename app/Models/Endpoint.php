<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    protected $fillable = [
        'uri',
        'frequency'
    ];

    public function statuses()
    {
        return $this->hasMany(Status::class)->orderBy('created_at', 'desc');
    }

    public function status()
    {
        return $this->hasOne(Status::class)->orderBy('created_at', 'desc');
    }

    public function isBackUp()
    {
        return $this->status->isUp() && ($this->statuses->get(1) && $this->statuses->get(1)->isDown());
    }
}
