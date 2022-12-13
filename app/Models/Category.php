<?php

namespace App\Models;

use App\Models\Post;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = array('category_name');
    public $timestapms = true;

    public function post()
    {
        return $this->belongsToMany('App\Models\Post');
    }

}
