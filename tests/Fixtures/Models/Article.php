<?php

namespace NovaChunkedVideo\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use NovaChunkedVideo\Tests\Fixtures\Factories\ArticleFactory;

class Article extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory(): ArticleFactory
    {
        return ArticleFactory::new();
    }
}
