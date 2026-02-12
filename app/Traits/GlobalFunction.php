<?php

namespace App\Traits;

use League\Fractal\Resource\Collection;

trait GlobalFunction
{
    function series($prefix, $model)
    {
        $model = "App\\" . $model;
        if($model::count() === 0) {
           return $series = $prefix.str_pad($model::count() + 1, 7, '0', STR_PAD_LEFT);
        }
        else {
           return $series = $prefix.str_pad($model::orderBy('id','desc')->first()->id + 1, 7, '0', STR_PAD_LEFT);
        }
    }
    function transform($collection, $transformer) {
        $new_collection = new Collection($collection, $transformer);
        $new_collection = $this->fractal->createData($new_collection);

        return $new_collection;
    }
    function uploadFile($image, $path, $file_name) {
        $extension = '';
        $image = $image;
        $image_explode = explode('base64,', $image);
        $image = $image_explode[1];

        $image = str_replace(' ', '+', $image);
        $imageName = $file_name.'.'.($image_explode[0] === "data:image/jpeg;"?'jpg':'png');
        \File::put(public_path($path).$imageName, base64_decode($image));

        return $imageName;
    }
}