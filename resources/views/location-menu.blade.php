<?php
use Illuminate\Support\Facades\Route;
?>

<ul id="location-drilldown-menu"
    class="vertical menu drilldown <?=(in_array(Route::currentRouteName(),array('studio'))) ? 'max-width-xl' : '' ?> <?=(in_array(Route::currentRouteName(),array('home','about'))) ? 'max-width-lg' : '' ?>"
    data-drilldown data-auto-height="true" data-animate-height="true"
>
    @foreach ($locationMenu as $menu)
        <!--
        <li>
            <a href="#" class="location-region">{{ $menu['region_name'] }}</a>
            <ul class="menu vertical nested">
            -->
                @foreach ($menu['areas'] as $area)
                    <li>
                        <a href="#" class="location-area">{{ $area['area_name'] }}</a>
                        <ul class="menu nested local grid-x">
                            @foreach ($area['locals'] as $k => $local)
                                @php
                                    $sourceOrder = 2 - $k;
                                @endphp
                                <li class="local cell small-12 @if ($area['nlocals'] == 4) large-3 @else large-4 @endif"
                                    @if (isset($localIdActive) and $localIdActive == $local['local_id']) data-active @endif
                                >
                                    <div class="local-links">
                                        {{ $local['local_name'] }}
                                        @foreach ($local['locations'] as $location)
                                            <a @click="gotoForecast" href="/forecast/{{ $location['slug'] }}">
                                                {!! $location['location_name'] !!}
                                            </a>
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
        <!--
            </ul>
        </li>
        -->
    @endforeach
</ul>
