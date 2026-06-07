{{-- Theme Previewer: visible when URL has ?theme_preview --}}
<div id="theme-previewer-widget" class="fixed bottom-4 left-4 z-[9999] bg-white rounded-lg shadow-2xl border border-gray-200 p-5 w-80" style="display: none;">
    <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-lg text-gray-800 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            Live Theme Preview
        </h3>
        <button onclick="document.getElementById('theme-previewer-widget').style.display='none';document.getElementById('theme-previewer-toggle').style.display='flex';" class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
    </div>
    <p class="text-xs text-gray-600 mb-5 leading-relaxed">
        Pick colors here to preview them live. When happy, copy the hex codes into <strong class="text-gray-800">Admin &rarr; Configuration &rarr; Design &rarr; Theme Colors</strong> to save permanently.
    </p>

    <div class="space-y-4">
        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <label class="text-sm font-medium text-gray-800">Primary Brand</label>
            <div class="flex items-center gap-2">
                <span id="hex-primary" class="text-xs text-gray-500 font-mono uppercase">{{ core()->getConfigData('general.design.theme_colors.primary_color') ?? '#060C3B' }}</span>
                <input type="color" class="h-8 w-8 rounded cursor-pointer border-0 p-0" oninput="updateThemeColor('--theme-navyBlue', this.value, 'hex-primary')" value="{{ core()->getConfigData('general.design.theme_colors.primary_color') ?? '#060C3B' }}">
            </div>
        </div>

        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <label class="text-sm font-medium text-gray-800">Button Background</label>
            <div class="flex items-center gap-2">
                <span id="hex-btn-bg" class="text-xs text-gray-500 font-mono uppercase">{{ core()->getConfigData('general.design.theme_colors.button_bg_color') ?? '#060C3B' }}</span>
                <input type="color" class="h-8 w-8 rounded cursor-pointer border-0 p-0" oninput="updateThemeColor('--theme-button-bg', this.value, 'hex-btn-bg')" value="{{ core()->getConfigData('general.design.theme_colors.button_bg_color') ?? '#060C3B' }}">
            </div>
        </div>

        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <label class="text-sm font-medium text-gray-800">Button Text</label>
            <div class="flex items-center gap-2">
                <span id="hex-btn-text" class="text-xs text-gray-500 font-mono uppercase">{{ core()->getConfigData('general.design.theme_colors.button_text_color') ?? '#ffffff' }}</span>
                <input type="color" class="h-8 w-8 rounded cursor-pointer border-0 p-0" oninput="updateThemeColor('--theme-button-text', this.value, 'hex-btn-text')" value="{{ core()->getConfigData('general.design.theme_colors.button_text_color') ?? '#ffffff' }}">
            </div>
        </div>

        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <label class="text-sm font-medium text-gray-800">Nav Text</label>
            <div class="flex items-center gap-2">
                <span id="hex-nav-text" class="text-xs text-gray-500 font-mono uppercase">{{ core()->getConfigData('general.design.theme_colors.nav_text_color') ?? '#060C3B' }}</span>
                <input type="color" class="h-8 w-8 rounded cursor-pointer border-0 p-0" oninput="updateThemeColor('--theme-nav-text', this.value, 'hex-nav-text')" value="{{ core()->getConfigData('general.design.theme_colors.nav_text_color') ?? '#060C3B' }}">
            </div>
        </div>

        <div class="flex justify-between items-center bg-gray-50 p-2 rounded">
            <label class="text-sm font-medium text-gray-800">Nav Hover Border</label>
            <div class="flex items-center gap-2">
                <span id="hex-nav-border" class="text-xs text-gray-500 font-mono uppercase">{{ core()->getConfigData('general.design.theme_colors.nav_border_color') ?? '#060C3B' }}</span>
                <input type="color" class="h-8 w-8 rounded cursor-pointer border-0 p-0" oninput="updateThemeColor('--theme-nav-border', this.value, 'hex-nav-border')" value="{{ core()->getConfigData('general.design.theme_colors.nav_border_color') ?? '#060C3B' }}">
            </div>
        </div>
    </div>
</div>

{{-- Floating toggle button — always in DOM, visibility controlled by JS below --}}
<button
    id="theme-previewer-toggle"
    onclick="document.getElementById('theme-previewer-widget').style.display='block';this.style.display='none';"
    class="fixed bottom-6 left-6 z-[9998] bg-indigo-600 text-white p-4 rounded-full shadow-2xl hover:bg-indigo-700 transition-transform transform hover:scale-110 items-center justify-center"
    style="display: none;"
    title="Open Live Theme Previewer"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
    </svg>
</button>

<script>
    // Activate the previewer toggle button when ?theme_preview is present in the URL
    (function () {
        var params = new URLSearchParams(window.location.search);
        if (params.has('theme_preview')) {
            var btn = document.getElementById('theme-previewer-toggle');
            if (btn) {
                btn.style.display = 'flex';
            }
        }
    })();

    function updateThemeColor(variable, value, labelId) {
        document.documentElement.style.setProperty(variable, value);
        var label = document.getElementById(labelId);
        if (label) label.innerText = value.toUpperCase();
    }
</script>
