<?php

namespace NovaChunkedVideo\Tests\Fixtures\Nova\Resources;

use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use NovaChunkedVideo\ChunkedVideo;

/**
 * @extends Resource<\NovaChunkedVideo\Tests\Fixtures\Models\Article>
 */
class Article extends Resource
{

    public static $model = \NovaChunkedVideo\Tests\Fixtures\Models\Article::class;

    public static $title = 'title';

    public static $search = [
        'title',
    ];

    public function fields(NovaRequest $request)
    {
        return [
            ChunkedVideo::make('Baz', 'video'),
        ];
    }
}
