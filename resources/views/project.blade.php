@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <p class="page__current-path"><strong>Current Path:</strong> {{ $currentPath }}</p>
    <div class="page__current-path">
        <div><strong>Инфо:</strong></div>
        <div><strong>Текущий проект:</strong> {{$configInfo['current_project']}}</div>
        <div><strong>Последняя проверка:</strong> {{$configInfo['last_checked']}}</div>
        <div><strong>Последняя синхронизация:</strong> {{$configInfo['last_synced']}}</div>
        <div><strong>Последний билд:</strong> {{$configInfo['last_build']}}</div>
    </div>
    <p class="page__child-projects"><strong>Child Projects:</strong></p>
    @foreach($child_projects as $project)
        <div class="page__child-project">{{ $project['name'] }}</div>
    @endforeach

    <div class="page__buttons">
        <div class="page__button">
            <button type="submit" onclick="commitFiles()">✅ All new files checked</button>
        </div>
        <div class="page__button">
            <button type="submit" onclick="copyChild()">🔄 Sync to Child</button>
        </div>
    </div>

    <div class="page__file-list">
        <div class="page__file-list-header">
            <div class="page__file-list-item">Item</div>
            <div class="page__file-list-item">Copy</div>
            <div class="page__file-list-item">Child Project</div>
        </div>

        @if ($currentPath !== config('w3x.parent_project'))
            <div class="page__link">
                <a href="{{ url('/') }}?path={{ rtrim(dirname($currentPath), '/') }}" class="page__link-text">
                    ...
                </a>
            </div>
        @endif

        @foreach ($directories as $directory)
            @php
                $item = $directory;
                $itemType = 'directory';
            @endphp
            @include('partials.file-directory-row', ['item' => $item, 'itemType' => $itemType])
        @endforeach

        @foreach ($files as $file)
            @php
                $item = $file;
                $itemType = 'file';
            @endphp
            @include('partials.file-directory-row', ['item' => $item, 'itemType' => $itemType])
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
        function updateCheckboxChange(itemPath, checkbox) {
            const path = cleanPath(itemPath, @json($rootPath));
            const copy = checkbox.checked ? 1 : 0;
            fetch('/update-copy-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    path: path,
                    copy: copy
                })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Updated:', data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        function commitFiles() {
            fetch('/commit', {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Commit successful:', data);
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error in commit:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during commit:', error);
                });
        }
        function copyChild() {
            fetch('/copy-child', {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Commit successful:', data);
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error in commit:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during commit:', error);
                });
        }
        function updateParentValue(itemPath, value) {
            const path = cleanPath(itemPath, @json($rootPath));
            fetch('/update-copy-child', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    path: path,
                    child: value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Commit successful");
                    } else {
                        console.log("Error during commit");
                    }
                })
                .catch(error => {
                    console.error("Error during commit:", error);
                });
        }
        function cleanPath(path, rootPath) {
            path = path.replace(/^directorys\[|\]$/g, '');
            path = path.replace(/^files\[|\]$/g, '');
            return path.replace(rootPath + '\\', '');
        }
    </script>
@endpush
