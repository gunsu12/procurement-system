@extends('adminlte::master')

@php
    $authType = $authType ?? 'login';
    $dashboardUrl = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboardUrl = $dashboardUrl ? route($dashboardUrl) : '';
    } else {
        $dashboardUrl = $dashboardUrl ? url($dashboardUrl) : '';
    }

    $bodyClasses = "{$authType}-page";

    if (! empty(config('adminlte.layout_dark_mode', null))) {
        $bodyClasses .= ' dark-mode';
    }
@endphp

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ $bodyClasses }}@stop

@section('body')
    <div class="{{ $authType }}-box">

        {{-- Logo --}}
        <div class="{{ $authType }}-logo">
            <a href="{{ $dashboardUrl }}">

                {{-- Symmetrical Logo Layout --}}
                <div class="d-flex align-items-center justify-content-center mb-2">
                    <div class="px-3">
                        @if (config('adminlte.auth_logo.enabled', false))
                            <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                                 alt="{{ config('adminlte.auth_logo.img.alt') }}"
                                 style="height: 80px; width: auto; object-fit: contain;">
                        @else
                            <img src="{{ asset(config('adminlte.logo_img')) }}"
                                 alt="{{ config('adminlte.logo_img_alt') }}" 
                                 style="height: 80px; width: auto; object-fit: contain;">
                        @endif
                    </div>
                    
                    <div class="px-3 border-left" style="border-left: 2px solid #dee2e6 !important;">
                        <img src="{{ asset('images/logo-rsia.png') }}" 
                             alt="RSIA Logo" 
                             style="height: 80px; width: auto; object-fit: contain;">
                    </div>
                </div>

                {{-- Logo Label 
                {!! config('adminlte.logo', '<b>Admin</b>LTE') !!} --}}

            </a>
        </div>

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }} mt-4">

            {{-- Card Header --}}
            @hasSection('auth_header')
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <h3 class="card-title float-none text-center">
                        @yield('auth_header')
                    </h3>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body {{ $authType }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
