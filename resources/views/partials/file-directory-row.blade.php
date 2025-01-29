<div
    style="display: grid; grid-template-columns: 70% 50px 150px; align-items: center; border-bottom: 1px solid black;"
    onmouseover="this.style.backgroundColor='#DCDCDC'"
    onmouseout="this.style.backgroundColor=''"
>
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
        <input type="hidden" name="{{ $itemType }}s[{{ $item['path'] }}]" value="0" style="width: 25px; height: 25px;">
        <input
            type="checkbox"
            name="{{ $itemType }}s[{{ $item['path'] }}]"
            value="1" {{ $item['copy'] ? 'checked' : '' }}
            style="width: 25px; height: 25px;"
            onchange="updateCheckboxChange('{{ addslashes($item['path']) }}', this)"
        >
    </label>
    <div style="width: 150px;">
        <input
            type="text"
            id="input-{{ $item['path'] }}"
            style="width: 100%;"
            value="{{ $item['copy_child'] }}"
            onchange="updateParentValue('{{ addslashes($item['path']) }}', this.value)"
        >
    </div>
</div>
