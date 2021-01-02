<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean',
//        'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
    ];

    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }
}
