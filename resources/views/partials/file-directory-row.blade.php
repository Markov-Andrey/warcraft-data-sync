<div class="item" onmouseover="this.classList.add('item--hover')" onmouseout="this.classList.remove('item--hover')">
    <div class="item__content">
        @if ($itemType === 'directory')
            <a href="{{ url('/') }}?path={{ $item['path'] }}" class="item__link" style="color: {{ $item['validated'] ? 'black' : 'green' }};">
                📂 {{ $item['name'] }}
            </a>
        @else
            <div class="item__text" style="color: {{ $item['validated'] ? 'black' : 'green' }};">
                📄 {{ $item['name'] }}
            </div>
        @endif
    </div>
    <div class="item__checkbox">
        <label>
            <input type="hidden" name="{{ $itemType }}s[{{ $item['path'] }}]" value="0" class="item__hidden-input">
            <input
                type="checkbox"
                name="{{ $itemType }}s[{{ $item['path'] }}]"
                value="1" {{ $item['copy'] ? 'checked' : '' }}
                class="item__checkbox-input"
                onchange="updateCheckboxChange('{{ addslashes($item['path']) }}', this)"
            >
        </label>
    </div>
    <div class="item__input">
        <input
            type="text"
            id="input-{{ $item['path'] }}"
            class="item__text-input"
            value="{{ $item['copy_child'] }}"
            onchange="updateParentValue('{{ addslashes($item['path']) }}', this.value)"
        >
    </div>
</div>
