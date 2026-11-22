<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::livewire('/categories', '⚡category-crud');

Route::livewire('/articles', '⚡article-list')->name('articles.index');
Route::livewire('/articles/create', '⚡article-form')->name('articles.create');
Route::livewire('/articles/{article}/edit', '⚡article-form')->name('articles.edit');
