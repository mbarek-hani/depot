<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\Present;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->withCount('products')
            ->withSum('products as quantity_total', 'quantity')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories->map(fn (Category $category) => Present::category($category))->all(),
            'colors' => collect(Category::COLORS)->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])->values()->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Category::class);

        $data = $this->validated($request);

        Category::create($data);

        return back()->with('status', 'تم إضافة الصنف بنجاح.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        Gate::authorize('update', $category);

        $data = $this->validated($request, $category);

        $category->update($data);

        return back()->with('status', 'تم تحديث الصنف بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'لا يمكن حذف صنف مرتبط بمنتجات. يرجى حذف منتجاته أولًا.']);
        }

        $category->delete();

        return back()->with('status', 'تم حذف الصنف بنجاح.');
    }

    protected function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category)],
            'color' => ['required', 'string', Rule::in(array_keys(Category::COLORS))],
            'description' => ['nullable', 'string', 'max:500'],
        ], [], [
            'name' => 'اسم الصنف',
            'color' => 'لون الصنف',
            'description' => 'الوصف',
        ]);
    }
}
