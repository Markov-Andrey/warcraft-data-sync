@extends('layouts.app')

@section('title', 'Project Explorer')

@section('content')
    <p><strong>Current Path:</strong> {{ $currentPath }}</p>
    <p><strong>Child Projects:</strong></p>
    @foreach($child_projects as $project)
        <div>{{ $project['name'] }}</div>
    @endforeach

    <div style="font-size: 25px">
        @csrf
        <p></p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; font-weight: bold;">
            <div>Item</div>
            <div>Copy</div>
        </div>

        @if ($currentPath !== config('w3x.parent_project'))
            <div>
                <a href="{{ url('/') }}?path={{ rtrim(dirname($currentPath), '/') }}">
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

        <div style="margin-top: 20px;">
            <button type="submit">Commit</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                let path = this.name.replace(/^directorys\[|\]$/g, '');
                path = path.replace(/^files\[|\]$/g, '');
                const copy = this.checked ? 1 : 0;

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
            });
        });
        document.querySelector('button[type="submit"]').addEventListener('click', function(event) {
            fetch('/commit', {
                method: 'GET',
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Commit successful:', data);
                })
                .catch(error => {
                    console.error('Error during commit:', error);
                });
        });
    </script>
@endpush
