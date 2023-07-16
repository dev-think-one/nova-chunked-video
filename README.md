# Laravel nova ChunkedVideo Field

![Packagist License](https://img.shields.io/packagist/l/think.studio/nova-chunked-video?color=%234dc71f)
[![Packagist Version](https://img.shields.io/packagist/v/think.studio/nova-chunked-video)](https://packagist.org/packages/think.studio/nova-chunked-video)
[![Total Downloads](https://img.shields.io/packagist/dt/think.studio/nova-chunked-video)](https://packagist.org/packages/think.studio/nova-chunked-video)
[![Build Status](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/badges/build.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/build-status/main)
[![Code Coverage](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/badges/coverage.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/?branch=main)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/dev-think-one/nova-chunked-video/?branch=main)

Laravel Nova field to upload big size video using chunks.

![](doc/assets/video-upload-example.gif)

## Versions targeting

| Nova | Package |
|------|---------|
| V1-3 | V1      |
| V4   | V2-3    |


## Installation

You can install the package via composer:

```bash
composer require think.studio/nova-chunked-video
# optional publish configs
php artisan vendor:publish --provider="NovaChunkedVideo\FieldServiceProvider" --tag="config"
```

## Usage

```php
\NovaChunkedVideo\ChunkedVideo::make( 'Video', 'big_video' )
    ->acceptedTypes( 'video/mp4' )
    ->disk( 'my_private_disk' )
    ->store( function ( $filePath, $disk, $model, $attribute, $request ) {
        // something like delete old video and save new
        $model->big_video = $filePath;
        $model->save();
        
        // WARNING: response should return url.
        return Storage::disk($disk)->url($filePath);
    } )
    ->preview( function ($value, $disk, $model ) {
        return Storage::disk($disk)->url($value);
    } )
    ->download(function (NovaRequest $request, Model $model, ?string $disk, $value) {
        return $value ? Storage::disk($disk)->download($value) : null;
    })
    ->delete(function (NovaRequest $request, $model, ?string $disk, $value) {
        if ($value) {
            Storage::disk($disk)->delete($value);
        }
    
        return true;
    })
    ->help( 'Usually a large video: 0.5-2GB. Max size 3GB' ),
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
