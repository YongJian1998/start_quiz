<?php

use App\Models\Article;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function articles()
    {
        return Article::query()
            ->with('category')
            ->filter(['title' => $this->search])
            ->latest()
            ->paginate(10);
    }

    public function delete(int $id): void
    {
        Article::findOrFail($id)->delete();
        session()->flash('message', 'Article deleted.');
    }
};
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Articles</h1>
        {{-- 普通 <a> 链接 + wire:navigate：SPA 式跳转，不整页 reload --}}
        <a href="{{ route('articles.create') }}" wire:navigate class="btn btn-primary">+ Add Article</a>
    </div>

    @if (session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search title..." class="form-control mb-3">

    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Category</th>
                <th>Status</th>
                <th>Published</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->articles as $article)
                <tr wire:key="article-{{ $article->id }}">
                    <td>{{ $article->id }}</td>
                    <td>{{ $article->title }}</td>
                    <td>{{ $article->category?->name }}</td>
                    <td>
                        <span @class(['badge', 'text-bg-success' => $article->status === 'active', 'text-bg-secondary' => $article->status !== 'active'])>
                            {{ $article->status }}
                        </span>
                    </td>
                    <td>{{ $article->published_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('articles.edit', $article) }}" wire:navigate class="btn btn-sm btn-outline-primary">Edit</a>
                        <button wire:click="delete({{ $article->id }})"
                                wire:confirm="Delete '{{ $article->title }}'?"
                                class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No articles found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $this->articles->links() }}
</div>
