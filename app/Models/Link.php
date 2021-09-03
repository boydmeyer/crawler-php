<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'url',
        'title',
        'description',
        'body',
        'image',
        'crawled',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
