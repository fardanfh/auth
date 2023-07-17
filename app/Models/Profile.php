<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Profile extends Model
{

    protected $fillable = array('user_id','first_name','last_name','summary','image');
    public $timestamps = true;
    
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
