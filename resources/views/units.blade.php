@extends('layouts.app')

@section('title', 'WarCraft Data Sync — Units')

@section('content')
    <div
        id="units-app"
        data-props="{{ json_encode([
            'units'   => $units,
            'tags'    => $tags,
            'allKeys' => $allKeys,
        ]) }}"
    ></div>
@endsection
