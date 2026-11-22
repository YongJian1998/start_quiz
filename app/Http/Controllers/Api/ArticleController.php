<?php

namespace App\Http\Controllers\Api;
use App\Models\Article;

use App\Http\Resources\ArticleResource;
use App\Http\Requests\IndexArticleRequest;

use App\Http\Controllers\Controller;


use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(IndexArticleRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $articles = Article::query()
            ->with('category')
            ->filter($filters)
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15)
            ->withQueryString();


        return ArticleResource::collection($articles);
    }
}
