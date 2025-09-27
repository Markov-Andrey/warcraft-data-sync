@extends('layouts.app')

@section('title', 'WarCraft Data Sync')

@section('content')
    <div class="page__button">
        <button>
            <a href="/units">Clear</a>
        </button>
        @foreach($tags as $tag)
            <button>
                <a href="?legends={{ urlencode(strtolower($tag)) }}">{{ $tag }}</a>
            </button>
        @endforeach
    </div>

    <div class="container">
        <table class="table table-bordered table-striped" style="border-collapse: collapse; width: 100%;">
            <thead class="table-dark">
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px;">Units</th>
                @if (!empty($allKeys))
                    @foreach ($allKeys as $key)
                        <th style="border: 1px solid #ddd; padding: 8px;">{{ $key }}</th>
                    @endforeach
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($units as $unitCode => $params)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;"><strong>{{ $unitCode }}</strong></td>
                    @foreach ($allKeys as $key)
                        @php
                            $paramData = $params[$key] ?? null;
                            $value = is_array($paramData) && isset($paramData['value']) ? $paramData['value'] : $paramData;
                        @endphp
                        <td style="border: 1px solid #ddd; padding: 8px;">
                            @if ($key === 'uico_png' && $value)
                                <img src="{{ asset('storage/png/' . ltrim(str_replace(storage_path('app/public') . '/', '', $value), '/')) }}"
                                     alt="{{ $unitCode }}"
                                     style="max-width: 64px; max-height: 64px;">
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('scripts')
@endpush
