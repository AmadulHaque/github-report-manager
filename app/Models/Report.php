<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'repository',
        'week',
        'commit_count',
        'report_content',
        'author',
        'start_date',
        'end_date'
    ];
}
