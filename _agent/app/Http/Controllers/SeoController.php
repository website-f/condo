<?php

namespace App\Http\Controllers;

use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeoController extends Controller
{
    public function index()
    {
        $username = Auth::guard('agent')->user()->username;
        $settings = SeoSetting::where('agent_username', $username)
            ->orderBy('page_type')
            ->paginate(20);

        return view('seo.index', compact('settings'));
    }

    public function create()
    {
        return view('seo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'page_type' => 'required|string|max:50',
            'page_identifier' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|image|max:2048',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50',
        ]);

        $data = $request->except('og_image');
        $data['agent_username'] = Auth::guard('agent')->user()->username;

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        SeoSetting::create($data);

        return redirect()->route('seo.index')->with('success', 'SEO setting created.');
    }

    public function edit(SeoSetting $seo)
    {
        $this->authorizeAgent($seo);
        return view('seo.edit', compact('seo'));
    }

    public function update(Request $request, SeoSetting $seo)
    {
        $this->authorizeAgent($seo);

        $request->validate([
            'page_type' => 'required|string|max:50',
            'page_identifier' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:60',
            'og_description' => 'nullable|string|max:160',
            'og_image' => 'nullable|image|max:2048',
            'canonical_url' => 'nullable|url|max:255',
            'robots' => 'nullable|string|max:50',
        ]);

        $data = $request->except('og_image');

        if ($request->hasFile('og_image')) {
            $data['og_image'] = $request->file('og_image')->store('seo', 'public');
        }

        $seo->update($data);

        return redirect()->route('seo.index')->with('success', 'SEO setting updated.');
    }

    public function destroy(SeoSetting $seo)
    {
        $this->authorizeAgent($seo);
        $seo->delete();
        return redirect()->route('seo.index')->with('success', 'SEO setting deleted.');
    }

    private function authorizeAgent(SeoSetting $seo): void
    {
        if ($seo->agent_username !== Auth::guard('agent')->user()->username) {
            abort(403);
        }
    }
}
