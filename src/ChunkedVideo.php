<?php

namespace Thinkone\ChunkedVideo;

use Laravel\Nova\Fields\AcceptsTypes;
use Laravel\Nova\Fields\Deletable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasPreview;
use Laravel\Nova\Fields\Storable;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChunkedVideo extends Field
{
    use Storable, HasPreview, AcceptsTypes, FileChunkSize, HasChunkFolder, Deletable, SupportsDependentFields;

    /**
     * @inheritDoc
     */
    public $component = 'chunked-video';

    /**
     * @inheritDoc
     */
    public $showOnCreation = false;

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        $this->chunksFolder = config('nova-chunked.tmp_chunks_folder');

        parent::__construct($name, $attribute, $resolveCallback);
    }

    /**
     * @inheritDoc
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        // skip fill
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'previewUrl'    => $this->resolvePreviewUrl(),
            'acceptedTypes' => $this->acceptedTypes,
            'maxSize'       => $this->getMaxSize(),
            'chunkSize'     => $this->getChunkSize(),
        ]);
    }
}
