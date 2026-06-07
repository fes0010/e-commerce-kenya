@props([
    'hasHeader'  => true,
    'hasFeature' => true,
    'hasFooter'  => true,
])

<!DOCTYPE html>

<html
    lang="{{ app()->getLocale() }}"
    dir="{{ core()->getCurrentLocale()->direction }}"
>
    <head>

        {!! view_render_event('bagisto.shop.layout.head.before') !!}

        <title>{{ $title ?? '' }}</title>

        <meta charset="UTF-8">

        <meta
            http-equiv="X-UA-Compatible"
            content="IE=edge"
        >
        <meta
            http-equiv="content-language"
            content="{{ app()->getLocale() }}"
        >

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >
        <meta
            name="base-url"
            content="{{ url()->to('/') }}"
        >
        <meta
            name="currency"
            content="{{ core()->getCurrentCurrency()->toJson() }}"
        >
        <meta 
            name="generator" 
            content="Bagisto"
        >

        @stack('meta')

        <link
            rel="icon"
            sizes="16x16"
            href="{{ core()->getCurrentChannel()->favicon_url ?? bagisto_asset('images/favicon.ico') }}"
        />

        @bagistoVite(['src/Resources/assets/css/app.css', 'src/Resources/assets/js/app.js'])

        <link
            rel="preconnect"
            href="https://fonts.googleapis.com"
            crossorigin
        />

        <link
            rel="preconnect"
            href="https://fonts.gstatic.com"
            crossorigin
        />

        <link
            rel="preload" as="style"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"
        />

        @stack('styles')

        @php
            $cfg = fn($k) => core()->getConfigData('general.design.theme_colors.'.$k);
            $themePreset = $cfg('theme_preset') ?? 'default';
            $bodyFont    = $cfg('body_font') ?? 'Poppins';
        @endphp

        <style>
            :root {
                @if($cfg('primary_color'))    --theme-navyBlue:    {{ $cfg('primary_color') }}; @endif
                @if($cfg('bg_color'))         --theme-lightOrange: {{ $cfg('bg_color') }}; @endif
                @if($cfg('accent_color'))     --theme-darkGreen:   {{ $cfg('accent_color') }}; @endif
                @if($cfg('link_color'))       --theme-darkBlue:    {{ $cfg('link_color') }}; @endif
                @if($cfg('danger_color'))     --theme-darkPink:    {{ $cfg('danger_color') }}; @endif
                @if($cfg('button_bg_color'))  --theme-button-bg:   {{ $cfg('button_bg_color') }}; @endif
                @if($cfg('button_text_color'))--theme-button-text: {{ $cfg('button_text_color') }}; @endif
                @if($cfg('nav_text_color'))   --theme-nav-text:    {{ $cfg('nav_text_color') }}; @endif
                @if($cfg('nav_border_color')) --theme-nav-border:  {{ $cfg('nav_border_color') }}; @endif
                @if($bodyFont !== 'Poppins')  --theme-font-poppins: "{{ $bodyFont }}"; @endif
            }
            {!! core()->getConfigData('general.content.custom_scripts.custom_css') !!}
        </style>

        {{-- Load saved body font from Google Fonts if not the default Poppins --}}
        @if($bodyFont !== 'Poppins')
        <link rel="stylesheet"
              href="https://fonts.googleapis.com/css2?family={{ urlencode($bodyFont) }}:wght@400;500;600;700;800&display=swap">
        @endif

        {{-- Apply pre-built theme class to body (matches app.css .theme-* selectors) --}}
        @if($themePreset !== 'default' && $themePreset !== 'custom')
        <script>document.addEventListener('DOMContentLoaded',function(){document.body.classList.add('theme-{{ $themePreset }}');});</script>
        @endif

        @if(core()->getConfigData('general.content.speculation_rules.enabled'))
            <script type="speculationrules">
                @json(core()->getSpeculationRules(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            </script>
        @endif

        {!! view_render_event('bagisto.shop.layout.head.after') !!}

    </head>

    <body>
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        {{-- Top progress bar: thin line that sweeps across while JS boots --}}
        <div id="nprogress-bar"></div>

        <a
            href="#main"
            class="skip-to-main-content-link"
        >
            Skip to main content
        </a>

        <!-- Built With Bagisto -->
        <div id="app">
            <!-- Flash Message Blade Component -->
            <x-shop::flash-group />

            <!-- Confirm Modal Blade Component -->
            <x-shop::modal.confirm />

            <!-- Page Header Blade Component -->
            @if ($hasHeader)
                <x-shop::layouts.header />
            @endif

            @if(
                core()->getConfigData('general.gdpr.settings.enabled')
                && core()->getConfigData('general.gdpr.cookie.enabled')
            )
                <x-shop::layouts.cookie />
            @endif

            {!! view_render_event('bagisto.shop.layout.content.before') !!}

            <!-- Page Content Blade Component -->
            <main id="main" class="bg-white">
                {{ $slot }}
            </main>

            {!! view_render_event('bagisto.shop.layout.content.after') !!}


            <!-- Page Services Blade Component -->
            @if ($hasFeature)
                <x-shop::layouts.services />
            @endif

            <!-- Page Footer Blade Component -->
            @if ($hasFooter)
                <x-shop::layouts.footer />
            @endif
        </div>

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        @stack('scripts')

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.before') !!}
        <script>
            /**
             * Mount the application as soon as the DOM is ready instead of waiting
             * for the `load` event. All `Vue` components are registered through
             * deferred `type="module"` scripts, which always finish executing
             * before `DOMContentLoaded` fires, so every component is available
             * by the time `app.mount()` runs. Mounting on `DOMContentLoaded`
             * avoids blocking the storefront behind every image/font download.
             */
            function mountApp() {
                app.mount("#app");
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", mountApp);
            } else {
                mountApp();
            }
        </script>

        {!! view_render_event('bagisto.shop.layout.vue-app-mount.after') !!}

        <script type="text/javascript">
            {!! core()->getConfigData('general.content.custom_scripts.custom_javascript') !!}
        </script>
        
        <x-shop::layouts.theme-previewer />

        <script>
        /* ── Micro progress bar ────────────────────────────────────────── */
        (function () {
            var bar = document.getElementById('nprogress-bar');
            var timer;

            function start() {
                clearTimeout(timer);
                bar.style.transition = 'none';
                bar.style.opacity    = '1';
                bar.classList.remove('done');
                bar.style.width = '2%';
                // Trickle to 85% over ~2 s
                requestAnimationFrame(function () {
                    bar.style.transition = 'width 1.8s cubic-bezier(.1,.6,.4,1)';
                    bar.style.width = '85%';
                });
            }

            function done() {
                clearTimeout(timer);
                bar.style.transition = 'width 0.15s ease';
                bar.style.width = '100%';
                timer = setTimeout(function () {
                    bar.classList.add('done');
                }, 180);
            }

            // Run on initial page load
            start();
            window.addEventListener('load', done);

            // Run on every same-page link click
            document.addEventListener('click', function (e) {
                var link = e.target.closest('a[href]');
                if (!link) return;
                var href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript') || link.target === '_blank') return;
                start();
            });
        })();

        /* ── Image reveal fade-in ──────────────────────────────────────── */
        (function () {
            function revealImg(img) {
                img.setAttribute('data-revealed', '1');
            }
            // For any img that loads after this script
            document.addEventListener('load', function (e) {
                if (e.target.tagName === 'IMG') revealImg(e.target);
            }, true);
            // For cached imgs that are already loaded at DOM-ready
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('img').forEach(function (img) {
                    if (img.complete) revealImg(img);
                });
            });
        })();
        </script>
    </body>
</html>
