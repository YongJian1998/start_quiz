<?php

use App\Models\Article;
use App\Models\Category;

use Livewire\WithPagination;
use Livewire\Component;

new class extends Component
{
    use WithPagination;

    public ?Article $article = null;

    // Search Parameters
    public $category_id = '';
    public $title = '';
    public $summary = '';
    public $body = '';
    public $perPage = 10;

    public function search()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset([
            'category_id',
            'title',
            'summary',
            'body',
        ]);

        $this->resetPage();
    }
    
    public function articles()
    {
        $query = Article::query();

        $query->when($this->category_id, function ($q) {
            $q->where('category_id', $this->category_id);
        });

        $query->when($this->title, function ($q) {
            $q->where('title', 'like', '%' . $this->title . '%');
        });

        $query->when($this->summary, function ($q) {
            $q->where('summary', 'like', '%' . $this->summary . '%');
        });

        $query->when($this->body, function ($q) {
            $q->where('body', 'like', '%' . $this->body . '%');
        });
        return $query->paginate($this->perPage);
    }

    public function show($id) 
    {
        $this->article = Article::with('category')->findOrFail($id);
    }

    public function back()
    {
        $this->article = null;
    }
};
?>

<div class="container py-5">
@if ($article)
    {{-- Article Detail --}}
    <button type="button" class="btn btn-link px-0 mb-3" wire:click="back">&larr; Back to articles</button>

    <article class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-3">
                <span class="badge text-bg-primary">{{ $article->category?->name ?? 'Uncategorized' }}</span>
                <small class="text-muted">{{ $article->published_at?->format('d M Y') }}</small>
            </div>
            <h1 class="h2 mb-3">{{ $article->title }}</h1>
            @if ($article->summary)
                <p class="lead text-muted">{{ $article->summary }}</p>
                <hr>
            @endif
            <div style="white-space: pre-line">{{ $article->body }}</div>
        </div>
    </article>
@else
    <h1 class="mb-4">Articles</h1>

    {{-- Search Filters --}}
    <form wire:submit="search" class="row g-2 mb-4">
        <div class="col-md-3">
            <input type="search" class="form-control" placeholder="Title"
                   wire:model.live.debounce.300ms="title" wire:input="search">
        </div>
        <div class="col-md-3">
            <input type="search" class="form-control" placeholder="Summary" wire:model="summary">
        </div>
        <div class="col-md-3">
            <input type="search" class="form-control" placeholder="Body" wire:model="body">
        </div>
        <div class="col-md-3">
            <select class="form-select" wire:model="category_id">
                <option value="">All categories</option>
                @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Search</button>
            <button type="button" class="btn btn-outline-secondary" wire:click="resetFilters">Reset</button>
        </div>
    </form>

    {{-- Article List --}}
    <div wire:loading.class="opacity-50">
        @forelse ($this->articles() as $item)
            <div class="card mb-3 shadow-sm" wire:key="article-{{ $item->id }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge text-bg-primary">{{ $item->category?->name ?? 'Uncategorized' }}</span>
                        <small class="text-muted">{{ $item->published_at?->format('d M Y') }}</small>
                    </div>
                    <h5 class="card-title">
                        <a href="#" wire:click.prevent="show({{ $item->id }})" class="stretched-link text-decoration-none">
                            {{ $item->title }}
                        </a>
                    </h5>
                    <p class="card-text text-muted mb-0">{{ $item->summary }}</p>
                </div>
            </div>
        @empty
            <div class="alert alert-light text-center">No articles found.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->articles()->links() }}
    </div>
@endif
</div>
