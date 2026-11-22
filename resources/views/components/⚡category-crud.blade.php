<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    #[Validate('required|string|max:255')]
    public string $name = '';

    // null = 新增模式, 有值 = 编辑模式
    public ?int $editingId = null;

    public bool $showModal = false;

    public function create(): void
    {
        $this->cancel();
        $this->showModal = true;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Category::query()
            ->withCount('articles')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);
    }

    // Create + Update 共用一个 save
    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update(['name' => $this->name]);
            session()->flash('message', 'Category updated.');
        } else {
            Category::create(['name' => $this->name]);
            session()->flash('message', 'Category created.');
        }

        $this->cancel();
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function cancel(): void
    {
        $this->reset('name', 'editingId', 'showModal');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        Category::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }

        session()->flash('message', 'Category deleted.');
    }
};
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Categories</h1>
        <button wire:click="create" class="btn btn-primary">+ Add Category</button>
    </div>

    @if (session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    {{-- Create / Edit modal：只在 $showModal = true 时渲染 --}}
    @if ($showModal)
        <div class="modal d-block" tabindex="-1" style="background: rgba(0,0,0,.5);"
             wire:keydown.escape.window="cancel">
            <div class="modal-dialog modal-dialog-centered">
                <form wire:submit="save" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit Category #'.$editingId : 'Add Category' }}</h5>
                        <button type="button" class="btn-close" wire:click="cancel"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Name</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancel" class="btn btn-outline-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Search --}}
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search..." class="form-control mb-3">

    {{-- List --}}
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Articles</th>
                <th>Created</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($this->categories as $category)
                <tr wire:key="category-{{ $category->id }}" @class(['table-warning' => $editingId === $category->id])>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->articles_count }}</td>
                    <td>{{ $category->created_at->diffForHumans() }}</td>
                    <td class="text-end">
                        <button wire:click="edit({{ $category->id }})" class="btn btn-sm btn-outline-primary">Edit</button>
                        <button wire:click="delete({{ $category->id }})"
                                wire:confirm="Delete '{{ $category->name }}'? Its {{ $category->articles_count }} article(s) will also be deleted."
                                class="btn btn-sm btn-outline-danger">Delete</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No categories found.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $this->categories->links() }}
</div>
