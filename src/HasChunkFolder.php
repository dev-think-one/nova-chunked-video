<?php


namespace NovaChunkedVideo;

use Laravel\Nova\Http\Requests\NovaRequest;

trait HasChunkFolder
{

    /**
     * Temporary chunk folder
     *
     * @var string
     */
    protected string $chunksFolder;

    /**
     * The callback used to store file.
     *
     * @var callable
     */
    public ?\Closure $storeFileCallback = null;

    /**
     * Set chunk folder
     *
     * @param string $chunksFolder
     *
     * @return $this
     */
    public function chunksFolder(string $chunksFolder)
    {
        $this->chunksFolder = $chunksFolder;

        return $this;
    }

    /**
     * Get chunk folder
     *
     * @return string
     */
    public function getChunksFolder(): string
    {
        return $this->chunksFolder;
    }

    /**
     * Specify the callback that should be used to save file.
     *
     * @param callable $storeFileCallback
     *
     * @return $this
     */
    public function store(callable $storeFileCallback)
    {
        $this->storeFileCallback = $storeFileCallback;

        return $this;
    }

    /**
     * Store file
     *
     * @param NovaRequest $request
     * @param $filePath
     *
     * @return string|null
     */
    public function storeFile(NovaRequest $request, $filePath): ?string
    {
        return call_user_func($this->storeFileCallback, $filePath, $this->getStorageDisk(), $this->resource, $this->attribute, $request);
    }
}
