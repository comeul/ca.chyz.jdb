<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fdr extends Model
{
    protected $connection = 'wordpress_fdr';
    protected $table = 'fdr';
    public $timestamps = false;
}
