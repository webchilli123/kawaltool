<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends BaseModel
{
    protected $table = 'brands';
    use SoftDeletes;
}
