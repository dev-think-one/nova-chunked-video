<?php

namespace Thinkone\ChunkedVideo;

trait FileChunkSize
{
    /**
     * The file max size
     *
     * @var ?numeric
     */
    protected $maxSize = null;

    /**
     * The chunk size
     *
     * @var ?numeric
     */
    protected $chunkSize = null;

    /**
     * Set the fields max sizes.
     *
     * @param $maxSize
     *
     * @return $this
     */
    public function maxSize($maxSize)
    {
        $this->maxSize = $maxSize;

        return $this;
    }

    /**
     * Set the chunk size.
     *
     * @param $chunkSize
     *
     * @return $this
     */
    public function chunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;

        return $this;
    }

    /**
     * Get the fields max sizes.
     *
     * @return float|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|int|mixed|string
     */
    public function getMaxSize()
    {
        return $this->maxSize ?? config('nova-chunked.validation.max_size');
    }

    /**
     * Get the chunk size.
     *
     * @return float|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|int|mixed|string
     */
    public function getChunkSize()
    {
        return $this->chunkSize ?? config('nova-chunked.validation.chunk_size');
    }
}
