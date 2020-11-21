<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tune extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'page_id',
    ];

    public function tune()
    {
        return $this->hasMany('App\Models\Page');
    }
}
