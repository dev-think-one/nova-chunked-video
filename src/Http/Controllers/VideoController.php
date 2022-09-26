<?php

namespace Thinkone\ChunkedVideo\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Thinkone\ChunkedVideo\ChunkedVideo;

class VideoController extends Controller
{
    public function store(NovaRequest $request)
    {
        $resource = $request->findResourceOrFail();

        $resource->authorizeToUpdate($request);

        $model = $resource->model();

        if (!$model || !$request->user()) {
            throw ValidationException::withMessages(['file' => 'not valid model']);
        }

        $fields = $resource->updateFields($request);

        /** @var ChunkedVideo|null $field */
        $field = $this->findField($request, $fields);
        if (!$field) {
            throw new \Exception("Not valid field [{$request->attribute}]");
        }

        $file      = $request->file('file');
        $storage   = Storage::disk($field->disk);
        $chunksDir = $field->getChunksFolder();

        $fileName = "{$model->getKey()}-{$request->user()->getKey()}-{$field->attribute}-{$file->getClientOriginalName()}";
        $path     = "{$chunksDir}{$fileName}";

        // Create empty file if file not exists
        if (!$storage->exists($path)) {
            $storage->put($path, '');
        }

        // Set optimal memory limit
        $newLimit   = ceil(($storage->size($path) + $file->getSize()) / 1000000) + 100;
        ini_set('memory_limit', "{$newLimit}M");
        // Append new content to file
        file_put_contents($storage->path($path), $file->get(), FILE_APPEND);

        // Validate file size
        if ($storage->size($path) > $field->getMaxSize()) {
            $storage->delete($path);

            throw ValidationException::withMessages(['file' => 'file greater than max size']);
        }

        if ($request->has('is_last') && $request->boolean('is_last')) {
            $fileName = basename($path, '.part');
            $newPath  = "{$chunksDir}{$fileName}";

            // remove old file if exists
            $storage->delete($newPath);
            // move file
            $storage->move($path, $newPath);

            $videoUrl = $field->storeFile($request, $newPath);

            return response()->json(['uploaded' => true, 'video_url' => $videoUrl]);
        }

        return response()->json(['uploaded' => true]);
    }

    /**
     * This function support "dependency container search"
     *
     * @param  NovaRequest  $request
     * @param $fields
     *
     * @return Field|null
     */
    protected function findField(NovaRequest $request, $fields)
    {
        /** @var Field $field */
        foreach ($fields as $field) {
            if (get_class($field) == 'Alexwenzel\DependencyContainer\DependencyContainer') {
                if (!empty($field->meta['fields'])) {
                    $field = $this->findField($request, $field->meta['fields']);
                    if ($field && $field instanceof ChunkedVideo) {
                        return $field;
                    }
                }
            } else {
                if ($field instanceof ChunkedVideo && !empty($field->attribute) && $field->attribute == $request->field) {
                    return $field;
                }
            }
        }

        return null;
    }
}
