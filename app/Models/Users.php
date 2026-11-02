<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    protected $fillable = array (
  0 => 'name',
  1 => 'email',
  2 => 'email_verified_at',
  3 => 'password',
  4 => 'remember_token',
);
}