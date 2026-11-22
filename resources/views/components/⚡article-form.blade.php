<?php

use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    // create 时是 null；edit 时由 route {article} 自动 model binding 进来
    public ?Article $article = null;

    public $category_id = '';
    public string $title = '';
    public string $summary = '';
    public string $body = '';
    public string $permalink = '';
    public string $status = Article::STATUS_ACTIVE;
    public ?string $published_at = null;

    public function mount(?Article $article = null): void
    {
        if ($article?->exists) {
            $this->article = $article;
            $this->fill($article->only('category_id', 'title', 'summary', 'body', 'permalink', 'status'));
            $this->published_at = $article->published_at?->format('Y-m-d\TH:i');
        }
    }

    protected function rules(): array
    {
        return [
            'category_id'  => ['required', 'exists:categories,id'],
            'title'        => ['required', 'string', 'max:255'],
            'summary'      => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            // unique 但 edit 时忽略自己
            'permalink'    => ['required', 'alpha_dash', 'max:255', Rule::unique('articles', 'permalink')->ignore($this->article?->id)],
            'status'       => ['required', Rule::in([Article::STATUS_ACTIVE, Article::STATUS_INACTIVE])],
            'published_at' => ['nullable', 'date'],
        ];
    }

    // 打 title 时，如果 permalink 还没手动填，自动生成 slug
    public function updatedTitle(string $value): void
    {
        if (! $this->article && $this->permalink === '') {
            $this->permalink = Str::slug($value);
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->article) {
            $this->article->update($data);
            session()->flash('message', 'Article updated.');
        } else {
            Article::create($data);
            session()->flash('message', 'Article created.');
        }

        // 保存后跳回 listing
        $this->redirectRoute('articles.index', navigate: true);
    }
};
?>

<div class="container py-4" style="max-width: 760px;">
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('articles.index') }}" wire:navigate class="btn btn-sm btn-outline-secondary">&larr; Back</a>
        <h1 class="h3 mb-0">{{ $article ? 'Edit Article #'.$article->id : 'Add Article' }}</h1>
    </div>

    <form wire:submit="save" class="card">
        <div class="card-body vstack gap-3">
            <div>
                <label class="form-label">Category</label>
                <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">-- Select --</option>
                    @foreach ($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Title</label>
                {{-- .blur：离开 input 才同步，触发 updatedTitle 生成 permalink --}}
                <input type="text" wire:model.blur="title" class="form-control @error('title') is-invalid @enderror">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Permalink</label>
                <input type="text" wire:model="permalink" class="form-control @error('permalink') is-invalid @enderror">
                @error('permalink') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Summary</label>
                <input type="text" wire:model="summary" class="form-control @error('summary') is-invalid @enderror">
                @error('summary') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Body</label>
                <textarea wire:model="body" rows="8" class="form-control @error('body') is-invalid @enderror"></textarea>
                @error('body') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Published at</label>
                    <input type="datetime-local" wire:model="published_at" class="form-control @error('published_at') is-invalid @enderror">
                    @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('articles.index') }}" wire:navigate class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                {{ $article ? 'Update' : 'Create' }}
            </button>
        </div>
    </form>
</div>
