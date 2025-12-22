@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboard_url = $dashboard_url ? route($dashboard_url) : '';
    } else {
        $dashboard_url = $dashboard_url ? url($dashboard_url) : '';
    }

    $user = Auth::user();
    $companyCode = $user && $user->company ? $user->company->code : 'BROS';

    $logo = 'images/logo-white.png';
    $brandName = '<b>BROS</b> Hospital';

    if ($companyCode === 'BIRO') {
        $logo = 'images/logo-rsia.png';
        $brandName = '<b>RSIA</b> Bali Royal';
    }
@endphp

<a href="{{ $dashboard_url }}" @if($layoutHelper->isLayoutTopnavEnabled())
class="navbar-brand {{ config('adminlte.classes_brand') }}" @else
class="brand-link {{ config('adminlte.classes_brand') }}" @endif>

    {{-- Small brand logo --}}
    <img src="{{ asset($logo) }}" alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
        class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}" style="max-height: 33px;">

    {{-- Brand text --}}
    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! $brandName !!}
    </span>

</a>