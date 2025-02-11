@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <div>
        <a href="/units">Clear</a>
        @foreach($tags as $tag)
            <a href="?legends={{ urlencode(strtolower($tag)) }}">{{ $tag }}</a>
        @endforeach
    </div>
    <div class="container">
        <table class="table table-bordered table-striped" style="border-collapse: collapse; width: 100%;">
            <thead class="table-dark">
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px;">Units</th>
                @if (!empty($units))
                    @php
                        $allParams = [];
                        foreach ($units as $unitCode => $params) {
                            foreach ($params as $param) {
                                if (!in_array($param['name'], $allParams)) {
                                    $allParams[] = $param['name'];
                                }
                            }
                        }
                    @endphp
                    @foreach ($allParams as $paramName)
                        <th style="border: 1px solid #ddd; padding: 8px;">{{ $paramName }}</th>
                    @endforeach
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($units as $unitCode => $params)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>{{ $unitCode }}</strong></td>
                    @foreach ($allParams as $paramName)
                        @php
                            $paramValue = null;
                            foreach ($params as $param) {
                                if ($param['name'] === $paramName) {
                                    $paramValue = $param['value'];
                                    break;
                                }
                            }
                        @endphp
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $paramValue }}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
@endpush
