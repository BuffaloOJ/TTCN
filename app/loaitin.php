<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class loaitin extends Model
{
    protected $table = 'loaitins';

    public function tintuc(){
    	return $this->hasMany('App\tintuc','idlt','id')->orderBy('created_at','desc');
    }
}
