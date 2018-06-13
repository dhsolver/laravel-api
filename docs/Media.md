## Media / Uploads
You can configure the media filesize and image dimension requirements in the config/junket.php.

File sizes can also be change from the .env file using the following variables:

```
MAX_IMAGE_KB=15000
MAX_AUDIO_KB=15000
```

### Images
Image processing is handled in the UploadsMedia trait using the [intervention/image](http://image.intervention.io) package.

Images are validated with Laravel's built in image file validation which allows the following mime types: jpeg, png, bmp, gif, or svg

### Audio

The only audio format accepted is MP3.
