<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $colors = Color::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->withCount('products')
            ->paginate(12)
            ->withQueryString();

        return view('admin.colors.index', compact('colors', 'search'));
    }

    public function create()
    {
        return view('admin.colors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:colors,slug'],
            'hex' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        Color::create($data);

        return redirect()->route('admin.colors.index')->with('status', 'Color created.');
    }

    public function edit(Color $color)
    {
        return view('admin.colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('colors', 'slug')->ignore($color->id)],
            'hex' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $color->update($data);

        return redirect()->route('admin.colors.index')->with('status', 'Color updated.');
    }

    public function destroy(Color $color)
    {
        if ($color->products()->exists()) {
            return redirect()
                ->route('admin.colors.index')
                ->with('error', 'Cannot delete a color while it is attached to products.');
        }

        $color->delete();

        return redirect()->route('admin.colors.index')->with('status', 'Color deleted.');
    }
}
