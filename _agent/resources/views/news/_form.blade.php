@php
    $publishAtValue = old('publish_at');

    if ($publishAtValue === null) {
        $rawPublishAt = trim((string) ($article->post_date ?? ''));

        if ($rawPublishAt !== '' && $rawPublishAt !== '0000-00-00 00:00:00') {
            try {
                $publishAtValue = \Carbon\Carbon::parse($rawPublishAt)->format('Y-m-d\TH:i');
            } catch (\Throwable) {
                $publishAtValue = null;
            }
        }
    }
@endphp

<div class="grid-2" style="grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); align-items:start;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">Content</div>

            <div class="form-group">
                <label class="form-label" for="title">Headline</label>
                <input id="title" type="text" name="title" class="form-input" value="{{ old('title', $article->post_title) }}" maxlength="255" placeholder="News headline" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" class="form-textarea" rows="3" placeholder="Short summary for the news list">{{ old('excerpt', $article->post_excerpt) }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label" for="content">Content</label>
                <textarea id="content" name="content" class="form-textarea" rows="18" placeholder="Write the full news content here..." required>{{ old('content', $article->post_content) }}</textarea>
                <div class="form-hint">HTML is allowed if you want richer formatting, embeds, or linked images inside the article body.</div>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">Publish</div>

            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach($statusOptions as $status)
                        <option value="{{ $status }}" @selected(old('status', $article->post_status ?: 'draft') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="publish_at">Publish Date</label>
                <input id="publish_at" type="datetime-local" name="publish_at" class="form-input" value="{{ $publishAtValue }}">
                <div class="form-hint">Leave this as-is if you just want to keep the current publish date and time.</div>
            </div>

            <div class="card" style="padding:16px;background:var(--accent-light);border-style:dashed;">
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.7;">
                    <strong style="display:block;color:var(--text);margin-bottom:6px;">WordPress-backed news</strong>
                    This editor writes directly into the shared `cd_posts` news table, so published posts will stay aligned with the existing WordPress news source.
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
                <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">{{ $submitLabel }}</button>
                <a href="{{ $cancelRoute }}" class="btn btn-secondary" style="flex:1;justify-content:center;">Cancel</a>
            </div>
        </div>
    </div>
</div>
