@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <div class="flex">
        <div>
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
                <div><strong>Last sync:</strong> {{ $configInfo['last_synced'] }}</div>
                <div><strong>Last build:</strong> {{ $configInfo['last_build'] }}</div>
                <div><strong>Build version:</strong>
                    <input type="text" value="{{ $configInfo['build_version'] }}" onchange="handleBuildChange(this)">
                </div>
            </div>
        </div>
        <div>
            <p class="page__child-projects"><strong>Child Projects:</strong></p>
            @foreach($child_projects as $key => $project)
                <div class="page__child-project">
                    @if($key == $configInfo['current_project'])
                        ✅
                    @endif
                    {{ $project['name'] }} ({{ $key }})
                </div>
            @endforeach
        </div>
    </div>

    <div class="page__buttons">
        <div class="page__button">
            <button type="submit" onclick="copyChild()">🔄 Sync to Child</button>
        </div>
        <div class="page__button">
            <button type="submit" onclick="setBuild()">🛠 Compile Build</button>
        </div>
    </div>

    <div class="page__constant-files">
        <p><strong>Copy Files (to copy from child):</strong></p>
        @foreach($copyFiles as $file)
            <div class="page__constant-file">
                📄 {{ basename($file) }}
            </div>
        @endforeach
    </div>

    <div class="page__exception-files mt-4">
        <p><strong>Files to remove (exceptions):</strong></p>
        @foreach($exceptionsFiles as $file)
            <div class="page__exception-file">
                ❌ {{ basename($file) }}
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script>
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
    </script>
@endpush
