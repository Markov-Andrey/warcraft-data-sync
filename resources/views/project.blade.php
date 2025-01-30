@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <p><strong>Current Path:</strong> {{ $currentPath }}</p>
    <p><strong>Child Projects:</strong></p>
    @foreach($child_projects as $project)
        <div>{{ $project['name'] }}</div>
    @endforeach

    <div style="display: flex; gap: 12px;">
        <div style="margin-top: 20px;">
            <button type="submit" onclick="commitFiles()">✅ All new files checked</button>
        </div>
        <div style="margin-top: 20px;">
            <button type="submit" onclick="copyChild()">🔄 Sync to Child</button>
        </div>
    </div>

    <div style="font-size: 15px">
        <p></p>
        <div style="display: grid; grid-template-columns: 70% 50px 150px; font-weight: bold;">
            <div>Item</div>
            <div>Copy</div>
            <div>Child Project</div>
        </div>

        @if ($currentPath !== config('w3x.parent_project'))
            <div>
                <a href="{{ url('/') }}?path={{ rtrim(dirname($currentPath), '/') }}" style="font-size: 25px">
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
                        // location.reload();
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
