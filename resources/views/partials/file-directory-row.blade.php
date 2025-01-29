<div style="display: grid; grid-template-columns: 1fr 1fr; align-items: center; margin-bottom: 10px;">
    @if ($itemType === 'directory')
        <a href="{{ url('/') }}?path={{ $item['path'] }}" style="color: {{ $item['validated'] ? 'black' : 'green' }};">
            📂 {{ $item['name'] }}
        </a>
    @else
        <div style="color: {{ $item['validated'] ? 'black' : 'green' }};">
            📄 {{ $item['name'] }}
        </div>
    @endif
    <label>
        <input type="hidden" name="{{ $itemType }}s[{{ $item['path'] }}]" value="0">
        <input type="checkbox" name="{{ $itemType }}s[{{ $item['path'] }}]" value="1" {{ $item['copy'] ? 'checked' : '' }}>
    </label>
</div>
