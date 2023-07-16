<?php

namespace NovaChunkedVideo\Tests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use NovaChunkedVideo\Tests\Fixtures\Nova\Resources\Article;

class StoreRequestTest extends TestCase
{

    protected \NovaChunkedVideo\Tests\Fixtures\Models\Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $this->article = \NovaChunkedVideo\Tests\Fixtures\Models\Article::factory()->create();
    }

    /** @test */
    public function upload_part()
    {
        $response = $this->post(route('nova.chunked-video.upload', [
            'resource'   => Article::uriKey(),
            'resourceId' => $this->article->getKey(),
            'field'      => 'video',
        ]), [
            'file' => UploadedFile::fake()->image('avatar.png', 10, 20),
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure(['uploaded']);
        $response->assertJsonPath('uploaded', true);
    }

    /** @test */
    public function upload_last_part()
    {
        $response = $this->post(route('nova.chunked-video.upload', [
            'resource'   => Article::uriKey(),
            'resourceId' => $this->article->getKey(),
            'field'      => 'video',
        ]), [
            'file'    => UploadedFile::fake()->image('avatar.png', 10, 20),
            'is_last' => true,
        ]);

        $response->assertSuccessful();

        $response->assertJsonStructure(['uploaded', 'video_url']);
        $response->assertJsonPath('uploaded', true);

        $this->assertNull($this->article->video);
        $this->article->refresh();
        $this->assertNotNull($this->article->video);

        $response->assertJsonPath('video_url', Storage::disk($this->defaultStorageName)->url($this->article->video));
    }
}
