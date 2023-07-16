# Laravel nova ChunkedVideo Field

Laravel Nova field to upload big size video using chunks.

![](doc/assets/video-upload-example.gif)

## Versions targeting

| Nova | Package |
|------|---------|
| V1-3 | V1      |
| V4   | V2      |


## Installation

You can install the package via composer:

```bash
composer require yaroslawww/nova-chunked-video
# optional publish configs
php artisan vendor:publish --provider="NovaChunkedVideo\FieldServiceProvider" --tag="config"
```

## Usage

```injectablephp
\NovaChunkedVideo\ChunkedVideo::make( 'Video', 'big_video' )
    ->acceptedTypes( 'video/mp4' )
    ->disk( 'my_private_disk' )
    ->store( function ( $filePath, $disk, $model, $attribute, $request ) {
        // something like delete old video and save new
        
        $model->big_video = $filePath;
        $model->save();
        
        return Storage::disk($disk)->url($filePath);
    } )
    ->preview( function ( $value, $disk, $model ) {
        // return preview url
        return Storage::disk($disk)->url($value);
    } )
    ->help( 'Usually a large video: 0.5-2GB. Max size 3GB' ),
```

## Credits

- [![Think Studio](https://yaroslawww.github.io/images/sponsors/packages/logo-think-studio.png)](https://think.studio/)
