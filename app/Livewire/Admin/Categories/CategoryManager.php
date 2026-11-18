<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryManager extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $parent = '';

    public function boot(): void
    {
        abort_unless(auth()->user()?->can('access-admin'), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status', 'parent'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'status', 'parent');
        $this->resetPage();
    }

    public function render()
    {
        $search = mb_substr(trim($this->search), 0, 150);
        $categories = Category::query()
            ->with('parent:id,name')
            ->withCount('primaryProducts')
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->when(in_array($this->status, ['active', 'inactive'], true),
                fn ($q) => $q->where('active', $this->status === 'active'))
            ->when($this->parent === 'root', fn ($q) => $q->whereNull('parent_id'))
            ->when(ctype_digit($this->parent), fn ($q) => $q->where('parent_id', (int) $this->parent))
            ->orderBy('name')->orderBy('id')->paginate(15);

        return view('livewire.admin.categories.category-manager', [
            'categories' => $categories,
            'parents' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
