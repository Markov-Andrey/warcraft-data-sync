@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <p class="page__current-path"><strong>Current Path:</strong> {{ $currentPath }}</p>
    <div class="page__current-path">
        <div><strong>Info:</strong></div>
        <div>
            <strong>Current project:</strong>
            <select name="child_project" id="child_project" onchange="handleProjectChange(this)">
                <option value="" disabled selected>-</option>
                @foreach ($child_projects as $key => $project)
                    <option value="{{ $key }}"
                        {{ $key === $configInfo['current_project'] ? 'selected' : '' }}>
                        {{ $project['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
        <div><strong>Last checked:</strong> {{$configInfo['last_checked']}}</div>
        <div><strong>Last sync:</strong> {{$configInfo['last_synced']}}</div>
        <div><strong>Last build:</strong> {{$configInfo['last_build']}}</div>
        <div><strong>Last version:</strong>
            <input type="text" value="{{$configInfo['build_version']}}" onchange="handleBuildChange(this)">
        </div>
    </div>
    <p class="page__child-projects"><strong>Child Projects:</strong></p>
    @foreach($child_projects as $key => $project)
        <div class="page__child-project">
            @if($key == $configInfo['current_project'])
                ✅
            @endif
            {{ $project['name'] }}
        </div>
    @endforeach

    <div class="page__buttons">
        <div class="page__button">
            <button type="submit" onclick="commitFiles()">✅ All new files checked</button>
        </div>
        <div class="page__button">
            <button type="submit" onclick="copyChild()">🔄 Sync to Child</button>
        </div>
        <div class="page__button">
            <button type="submit" onclick="setBuild()">🛠 Compile Build</button>
        </div>
    </div>

    <div class="page__file-list">
        <div class="page__file-list-header">
            <div class="page__file-list-item">Item</div>
            <div class="page__file-list-item">Copy</div>
            <div class="page__file-list-item">Child Project</div>
        </div>

        @if ($currentPath !== env('PARENT_PROJECT'))
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
            @include('partials.file-directory-row', ['item' => $item, 'itemType' => $itemType, 'constantFiles' => $constantFiles])
        @endforeach

        @foreach ($files as $file)
            @php
                $item = $file;
                $itemType = 'file';
            @endphp
            @include('partials.file-directory-row', ['item' => $item, 'itemType' => $itemType, 'constantFiles' => $constantFiles])
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
        function setBuild() {
            fetch('/set-build', {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Build successful:', data);
                    if (data.success) {
                        location.reload();
                    } else {
                        console.error('Error in build:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error during build:', error);
                });
        }
        function handleProjectChange(selectElement) {
            const selectedProjectKey = selectElement.value;
            fetch('/switch-project', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    select: selectedProjectKey
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Switch successful");
                        location.reload();
                    } else {
                        console.log("Error during switch");
                    }
                })
                .catch(error => {
                    console.error("Error during switch:", error);
                });
        }
        function handleBuildChange(text) {
            const version = text.value;
            fetch('/update-version', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    version: version
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("Version successful");
                        location.reload();
                    } else {
                        console.log("Error during version");
                    }
                })
                .catch(error => {
                    console.error("Error during version:", error);
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
