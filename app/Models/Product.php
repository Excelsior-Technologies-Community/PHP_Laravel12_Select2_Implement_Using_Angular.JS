<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['title','price'];

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }
}
