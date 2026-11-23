<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;
class Inspector extends Model
{
  protected $primaryKey = 'employee_no';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'employee_no', // رقم الموظف
        'title', // المسمى الوظيفي
        'photo_url',// رابط الصورة
        'region_id',
        'supervisor_no',
    ];
 
    

    
}
