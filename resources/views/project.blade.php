@extends('layouts.app')

@section('title', 'Project Explorer')

@section('content')
    <p><strong>Current Path:</strong> {{ $currentPath }}</p>

    <form method="POST" action="{{ route('updateConfig') }}" style="font-size: 25px">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; font-weight: bold;">
            <div>Item</div>
            <div>Copy</div>
        </div>

        @foreach ($directories as $directory)
            <div style="display: grid; grid-template-columns: 1fr 1fr; align-items: center">
                <a href="{{ url('/') }}?path={{ $currentPath . DIRECTORY_SEPARATOR . $directory }}">
                    📂 {{ $directory }}
                </a>
                <label>
                    <input type="hidden" name="directories[{{ $directory }}]" value="0">
                    <input type="checkbox" name="directories[{{ $directory }}]" value="1" {{ true ? 'checked' : '' }}>
                </label>
            </div>
        @endforeach

        @foreach ($files as $file)
            <div style="display: grid; grid-template-columns: 1fr 1fr; align-items: center; margin-bottom: 10px;">
                <div>
                    📄 {{ $file }}
                </div>
                <label>
                    <input type="hidden" name="files[{{ $file }}]" value="0">
                    <input type="checkbox" name="files[{{ $file }}]" value="1" {{ true ? 'checked' : '' }}>
                </label>
            </div>
        @endforeach

        <div style="margin-top: 20px;">
            <button type="submit">Save Changes</button>
        </div>
    </form>

    @if ($currentPath !== config('w3x.parent_project'))
        <p>
            <a href="{{ url('/') }}?path={{ dirname($currentPath) }}">Go Back</a>
        </p>
    @endif
@endsection
