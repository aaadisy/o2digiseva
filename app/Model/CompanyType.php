<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CompanyType extends Model
{
    protected $table = 'company_types';

    protected $fillable = [
        'mccCode',
        'mccDescription'
    ];
}
