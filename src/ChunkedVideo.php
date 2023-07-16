<?php

namespace NovaChunkedVideo;

use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Contracts\Deletable as DeletableContract;
use Laravel\Nova\Contracts\Downloadable as DownloadableContract;
use Laravel\Nova\Contracts\Storable as StorableContract;
use Laravel\Nova\Fields\AcceptsTypes;
use Laravel\Nova\Fields\Deletable;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasDownload;
use Laravel\Nova\Fields\HasPreview;
use Laravel\Nova\Fields\HasThumbnail;
use Laravel\Nova\Fields\Storable;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ChunkedVideo extends Field implements StorableContract, DeletableContract, DownloadableContract
{
    use Storable;
    use HasPreview;
    use HasThumbnail;
    use AcceptsTypes;
    use Deletable;
    use HasDownload;
    use SupportsDependentFields;
    use FileChunkSize;
    use HasChunkFolder;

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

        $this->delete(function () {
            if ($this->value) {
                Storage::disk($this->getStorageDisk())->delete($this->value);

                return [$this->attribute => null];
            }

            return null;
        });
    }

    /**
     * @inheritDoc
     */
    protected function fillAttribute(NovaRequest $request, $requestAttribute, $model, $attribute)
    {
        // skip fill
    }

    public function getStorageDisk()
    {
        return $this->disk ?: $this->getDefaultStorageDisk();
    }

    /**
     * Get the full path that the field is stored at on disk.
     *
     * @return string|null
     */
    public function getStoragePath()
    {
        return $this->value;
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $previewUrl = $this->resolvePreviewUrl();

        return array_merge(parent::jsonSerialize(), [
            'thumbnailUrl'  => $this->resolveThumbnailUrl(),
            'previewUrl'    => $previewUrl,
            'downloadable'  => $this->downloadsAreEnabled   && isset($this->downloadResponseCallback) && !empty($previewUrl),
            'deletable'     => isset($this->deleteCallback) && $this->deletable,
            'acceptedTypes' => $this->acceptedTypes,
            'maxSize'       => $this->getMaxSize(),
            'chunkSize'     => $this->getChunkSize(),
        ]);
    }
}
