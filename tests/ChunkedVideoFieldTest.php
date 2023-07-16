<?php

namespace NovaChunkedVideo\Tests;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Http\Requests\NovaRequest;
use NovaChunkedVideo\ChunkedVideo;
use NovaChunkedVideo\Tests\Fixtures\Models\Article;

class ChunkedVideoFieldTest extends TestCase
{
    /** @test */
    public function component_returns_correct_value()
    {
        $this->assertEquals('chunked-video', ChunkedVideo::make('Foo')->component());
    }

    /** @test */
    public function fill_attribute_do_nothing()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        /** @var NovaRequest $request */
        $request = app(NovaRequest::class);
        $request->merge(['bar' => 'newValTest',]);
        $model      = new Article();
        $model->qux = 'test';

        $field->fillInto($request, $model, 'qux', 'bar');

        $this->assertEquals('test', $model->qux);
    }

    /** @test */
    public function storage_disk_is_changeable()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        $this->assertEquals($this->defaultStorageName, $field->getStorageDisk());

        $field->disk('foo_bar');

        $this->assertEquals('foo_bar', $field->getStorageDisk());
    }

    /** @test */
    public function default_storage_path_is_value()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        $this->assertNull($field->getStoragePath());

        $field->setValue('foo_bar_qux');

        $this->assertEquals('foo_bar_qux', $field->getStoragePath());
    }

    /** @test */
    public function field_by_default_has_delete_callback()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        $this->assertTrue(is_callable($field->deleteCallback));
    }

    /** @test */
    public function default_delete_callback_returns_null_without_value()
    {
        $field   = ChunkedVideo::make('Foo', 'qux');
        $request = app(NovaRequest::class);
        $model   = new Article();

        $result = call_user_func_array($field->deleteCallback, [$request, $model, $field->getStorageDisk(), $field->getStoragePath()]);

        $this->assertNull($result);
        //$field->setValue('foo_bar_qux');
    }

    /** @test */
    public function default_delete_callback_success_deleted()
    {
        $field   = ChunkedVideo::make('Foo', 'qux');
        $request = app(NovaRequest::class);
        $model   = new Article();

        $fileName = 'foo_bar_qux.txt';
        $field->setValue($fileName);

        Storage::disk($this->defaultStorageName)->put($fileName, 'xx');

        Storage::disk($this->defaultStorageName)->assertExists($fileName);
        $result = call_user_func_array($field->deleteCallback, [$request, $model, $field->getStorageDisk(), $field->getStoragePath()]);

        Storage::disk($this->defaultStorageName)->assertMissing($fileName);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('qux', $result);
        $this->assertNull($result['qux']);
    }

    /** @test */
    public function chunk_size_is_changeable()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        $this->assertEquals(config('nova-chunked.validation.chunk_size'), $field->getChunkSize());
        $this->assertEquals(config('nova-chunked.validation.max_size'), $field->getMaxSize());

        $field->chunkSize(333)
            ->maxSize(444);

        $this->assertEquals(333, $field->getChunkSize());
        $this->assertEquals(444, $field->getMaxSize());
    }

    /** @test */
    public function chunk_folder_is_changeable()
    {
        $field = ChunkedVideo::make('Foo', 'qux');

        $this->assertEquals(config('nova-chunked.tmp_chunks_folder'), $field->getChunksFolder());

        $field->chunksFolder('my-fold');

        $this->assertEquals('my-fold', $field->getChunksFolder());
    }

    /** @test */
    public function chunk_custom_store_callback()
    {
        $field           = ChunkedVideo::make('Foo', 'qux');
        $field->resource = new Article();

        // Has default callback
        $this->assertTrue(is_callable($field->storeFileCallback));

        // Provide new callback
        $field->store(function ($filePath, $disk, $model, $attribute, $request) {
            $this->assertEquals('new_path', $filePath);
            $this->assertInstanceOf(Article::class, $model);
            $this->assertEquals('qux', $attribute);
            $this->assertInstanceOf(NovaRequest::class, $request);

            return 'foo_bar_baz';
        });

        $this->assertTrue(is_callable($field->storeFileCallback));

        $request = app(NovaRequest::class);

        $response = $field->storeFile($request, 'new_path');

        $this->assertEquals('foo_bar_baz', $response);
    }

    /** @test */
    public function check_fies_json()
    {
        $field = ChunkedVideo::make('Foo', 'qux')
            ->acceptedTypes('txt')
        ->chunkSize(22)
        ->maxSize(33)
        ->download(fn () => '');

        $field->value = 'my-file.vid';

        $data = $field->jsonSerialize();

        $this->assertIsArray($data);
        $this->assertNull($data['thumbnailUrl']);
        $this->assertTrue($data['deletable']);
        $this->assertTrue($data['downloadable']);
        $this->assertEquals(Storage::disk($field->getStorageDisk())->url($field->value), $data['previewUrl']);
        $this->assertEquals(22, $data['chunkSize']);
        $this->assertEquals(33, $data['maxSize']);
        $this->assertEquals('txt', $data['acceptedTypes']);

    }

}
