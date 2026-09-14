<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * 投稿の型の一覧
     */
    public function index(Request $request)
    {
        $templates = $request->user()->templates()->orderBy('name')->get();

        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateTemplate($request);

        $request->user()->templates()->create($validated);

        return redirect()->route('templates.index');
    }

    public function edit(Template $template)
    {
        $this->authorizeOwner($template);

        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $this->authorizeOwner($template);

        $template->update($this->validateTemplate($request));

        return redirect()->route('templates.index');
    }

    public function destroy(Template $template)
    {
        $this->authorizeOwner($template);

        $template->delete();

        return redirect()->route('templates.index');
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'name' => 'required|max:255',
            'body' => 'required|max:5000',
        ]);
    }

    private function authorizeOwner(Template $template): void
    {
        abort_if($template->user_id !== auth()->id(), 403);
    }
}
