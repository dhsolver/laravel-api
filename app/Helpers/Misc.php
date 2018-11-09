<?php

if (! function_exists('modelId')) {
    function modelId($model)
    {
        if (is_object($model)) {
            return $model->id;
        } elseif (is_array($model)) {
            return $model['id'];
        }

        return $model;
    }
}
