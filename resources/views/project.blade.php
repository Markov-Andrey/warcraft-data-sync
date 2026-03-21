@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <div
        id="project-app"
        data-props="{{ json_encode([
            'configInfo'      => $configInfo,
            'childProjects'   => $child_projects,
            'copyFiles'       => array_values($copyFiles),
            'exceptionsFiles' => array_values($exceptionsFiles),
        ]) }}"
    ></div>
@endsection
