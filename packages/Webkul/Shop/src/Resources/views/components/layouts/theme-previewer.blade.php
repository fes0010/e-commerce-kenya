{{--
    Theme Previewer Widget
    Activate by visiting any page with ?theme_preview in the URL
    e.g. https://yourstore.com/?theme_preview
--}}

{{-- Widget Panel (hidden by default) --}}
<div id="tp-widget" style="
    display: none;
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 99999;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    border: 1px solid #e5e7eb;
    width: 340px;
    font-family: sans-serif;
    max-height: 92vh;
    overflow-y: auto;
">
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 18px 12px; border-bottom:1px solid #f3f4f6; position:sticky; top:0; background:#fff; z-index:1; border-radius:14px 14px 0 0;">
        <strong style="font-size:15px; color:#1f2937;">🎨 Live Theme Studio</strong>
        <button
            onclick="document.getElementById('tp-widget').style.display='none'; document.getElementById('tp-btn').style.display='flex';"
            style="background:none; border:none; font-size:22px; cursor:pointer; color:#6b7280; line-height:1; padding:0 4px;"
        >&times;</button>
    </div>

    <div style="padding: 14px 18px 18px;">

        {{-- ===== SECTION: Pre-built Themes ===== --}}
        <div style="margin-bottom:18px;">
            <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin:0 0 10px;">Quick Themes</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">

                <button onclick="tpApplyTheme('default')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left; transition:border-color .15s;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#060C3B;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#F6F2EB;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#40994A;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Default</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Poppins · Navy</span>
                </button>

                <button onclick="tpApplyTheme('sunset')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#2D142C;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#FEECE9;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#E23E57;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Sunset</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Outfit · Deep Plum</span>
                </button>

                <button onclick="tpApplyTheme('forest')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#132A13;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#ECF39E;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#4F772D;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Forest</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Inter · Deep Green</span>
                </button>

                <button onclick="tpApplyTheme('ocean')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#0F2027;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#E8F1F2;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#2A9D8F;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Ocean</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Roboto · Deep Teal</span>
                </button>

                <button onclick="tpApplyTheme('red')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#8B0000;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#FFEEEE;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#DC143C;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Red</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Poppins · Crimson</span>
                </button>

                <button onclick="tpApplyTheme('maroon')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                    <div style="display:flex; gap:4px; margin-bottom:6px;">
                        <span style="width:18px; height:18px; border-radius:50%; background:#4A0404;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#FDF5F5;"></span>
                        <span style="width:18px; height:18px; border-radius:50%; background:#C2185B;"></span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:#374151;">Maroon</span><br>
                    <span style="font-size:10px; color:#9ca3af;">Inter · Dark Maroon</span>
                </button>

            </div>
        </div>

        <div style="height:1px; background:#f3f4f6; margin:0 0 16px;"></div>

        {{-- ===== SECTION: Font ===== --}}
        <div style="margin-bottom:18px;">
            <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin:0 0 10px;">Body Font</p>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                @foreach(['Poppins','Inter','Outfit','Roboto','Lato','Nunito'] as $font)
                <button onclick="tpSetFont('{{ $font }}')"
                    style="border:2px solid #e5e7eb; border-radius:8px; padding:8px 10px; cursor:pointer; background:#fff; font-family:'{{ $font }}', sans-serif; font-size:13px; color:#374151; text-align:center;"
                    onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'"
                >{{ $font }}</button>
                @endforeach
            </div>
        </div>

        <div style="height:1px; background:#f3f4f6; margin:0 0 16px;"></div>

        {{-- ===== SECTION: Colors ===== --}}
        <div style="margin-bottom:6px;">
            <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin:0 0 10px;">Individual Colors</p>

            @php
            $colorFields = [
                ['id'=>'tp-primary',    'var'=>'--theme-navyBlue',       'label'=>'Primary / Nav',      'key'=>'primary_color',    'default'=>'#060C3B'],
                ['id'=>'tp-bg',         'var'=>'--theme-lightOrange',    'label'=>'Page Background',    'key'=>'bg_color',         'default'=>'#F6F2EB'],
                ['id'=>'tp-accent',     'var'=>'--theme-darkGreen',      'label'=>'Accent / Badge',     'key'=>'accent_color',     'default'=>'#40994A'],
                ['id'=>'tp-link',       'var'=>'--theme-darkBlue',       'label'=>'Links / Highlights', 'key'=>'link_color',       'default'=>'#0044F2'],
                ['id'=>'tp-danger',     'var'=>'--theme-darkPink',       'label'=>'Sale / Alert',       'key'=>'danger_color',     'default'=>'#F85156'],
                ['id'=>'tp-btn-bg',     'var'=>'--theme-button-bg',      'label'=>'Button Background',  'key'=>'button_bg_color',  'default'=>'#060C3B'],
                ['id'=>'tp-btn-text',   'var'=>'--theme-button-text',    'label'=>'Button Text',        'key'=>'button_text_color','default'=>'#ffffff'],
                ['id'=>'tp-nav-text',   'var'=>'--theme-nav-text',       'label'=>'Nav Menu Text',      'key'=>'nav_text_color',   'default'=>'#060C3B'],
                ['id'=>'tp-nav-border', 'var'=>'--theme-nav-border',     'label'=>'Nav Hover Line',     'key'=>'nav_border_color', 'default'=>'#060C3B'],
            ];
            @endphp

            @foreach($colorFields as $field)
            <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px; margin-bottom:6px;">
                <label style="font-size:12px; font-weight:500; color:#374151;">{{ $field['label'] }}</label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <code id="{{ $field['id'] }}" style="font-size:10px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.'.$field['key']) ?? $field['default'] }}</code>
                    <input type="color"
                        value="{{ core()->getConfigData('general.design.theme_colors.'.$field['key']) ?? $field['default'] }}"
                        oninput="tpColor('{{ $field['var'] }}', this.value, '{{ $field['id'] }}')"
                        style="width:30px; height:30px; border:none; padding:0; cursor:pointer; border-radius:4px; background:none;">
                </div>
            </div>
            @endforeach
        </div>

        {{-- Save reminder --}}
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 12px; margin-top:14px;">
            <p style="font-size:11px; color:#1e40af; margin:0; line-height:1.5;">
                💾 <strong>To save permanently:</strong> copy hex codes into<br>
                <strong>Admin → Configuration → Design → Theme Colors</strong>
            </p>
        </div>

    </div>
</div>

{{-- Floating toggle button --}}
<button id="tp-btn"
    onclick="document.getElementById('tp-widget').style.display='block'; this.style.display='none';"
    style="
        display: none;
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 99998;
        background: #4f46e5;
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 56px;
        height: 56px;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(79,70,229,0.5);
        align-items: center;
        justify-content: center;
    "
    onmouseover="this.style.transform='scale(1.1)'"
    onmouseout="this.style.transform='scale(1)'"
    title="Open Theme Studio"
>🎨</button>

<script>
    // Theme data matching app.css definitions
    var TP_THEMES = {
        'default': {
            '--theme-navyBlue':    '#060C3B',
            '--theme-lightOrange': '#F6F2EB',
            '--theme-darkGreen':   '#40994A',
            '--theme-darkBlue':    '#0044F2',
            '--theme-darkPink':    '#F85156',
            'font': 'Poppins',
        },
        'sunset': {
            '--theme-navyBlue':    '#2D142C',
            '--theme-lightOrange': '#FEECE9',
            '--theme-darkGreen':   '#3E8E7E',
            '--theme-darkBlue':    '#51C4D3',
            '--theme-darkPink':    '#E23E57',
            'font': 'Outfit',
        },
        'forest': {
            '--theme-navyBlue':    '#132A13',
            '--theme-lightOrange': '#ECF39E',
            '--theme-darkGreen':   '#4F772D',
            '--theme-darkBlue':    '#31572C',
            '--theme-darkPink':    '#90A955',
            'font': 'Inter',
        },
        'ocean': {
            '--theme-navyBlue':    '#0F2027',
            '--theme-lightOrange': '#E8F1F2',
            '--theme-darkGreen':   '#2A9D8F',
            '--theme-darkBlue':    '#203A43',
            '--theme-darkPink':    '#2C5364',
            'font': 'Roboto',
        },
        'red': {
            '--theme-navyBlue':    '#8B0000',
            '--theme-lightOrange': '#FFEEEE',
            '--theme-darkGreen':   '#228B22',
            '--theme-darkBlue':    '#B22222',
            '--theme-darkPink':    '#DC143C',
            'font': 'Poppins',
        },
        'maroon': {
            '--theme-navyBlue':    '#4A0404',
            '--theme-lightOrange': '#FDF5F5',
            '--theme-darkGreen':   '#2E5C2E',
            '--theme-darkBlue':    '#800000',
            '--theme-darkPink':    '#C2185B',
            'font': 'Inter',
        },
    };

    // Show the button when ?theme_preview is in the URL
    (function () {
        if (new URLSearchParams(window.location.search).has('theme_preview')) {
            document.getElementById('tp-btn').style.display = 'flex';
        }
    })();

    // Apply a full pre-built theme
    function tpApplyTheme(name) {
        var t = TP_THEMES[name];
        if (!t) return;
        var root = document.documentElement;
        // Apply all color variables
        Object.keys(t).forEach(function(k) {
            if (k !== 'font') root.style.setProperty(k, t[k]);
        });
        // Also sync button/nav to primary by default
        root.style.setProperty('--theme-button-bg',   t['--theme-navyBlue']);
        root.style.setProperty('--theme-nav-text',    t['--theme-navyBlue']);
        root.style.setProperty('--theme-nav-border',  t['--theme-navyBlue']);
        // Apply font
        if (t.font) tpSetFont(t.font);
        // Update color picker values in the panel
        var map = {
            'tp-primary':    '--theme-navyBlue',
            'tp-bg':         '--theme-lightOrange',
            'tp-accent':     '--theme-darkGreen',
            'tp-link':       '--theme-darkBlue',
            'tp-danger':     '--theme-darkPink',
            'tp-btn-bg':     '--theme-navyBlue',
            'tp-nav-text':   '--theme-navyBlue',
            'tp-nav-border': '--theme-navyBlue',
        };
        Object.keys(map).forEach(function(id) {
            var val = t[map[id]];
            var el = document.getElementById(id);
            if (el && val) el.innerText = val.toUpperCase();
            // Update the color input next to the label
            var inputEl = el ? el.nextElementSibling : null;
            if (inputEl && inputEl.type === 'color') inputEl.value = val;
        });
    }

    // Set body font
    function tpSetFont(fontName) {
        // Load from Google Fonts if not already loaded
        var linkId = 'tp-font-' + fontName.replace(/\s/g,'');
        if (!document.getElementById(linkId)) {
            var link = document.createElement('link');
            link.id = linkId;
            link.rel = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(fontName) + ':wght@400;500;600;700&display=swap';
            document.head.appendChild(link);
        }
        // Apply to root variables and body
        document.documentElement.style.setProperty('--theme-font-poppins', '"' + fontName + '"');
        document.body.style.fontFamily = '"' + fontName + '", sans-serif';
    }

    // Update a single CSS variable
    function tpColor(variable, value, labelId) {
        document.documentElement.style.setProperty(variable, value);
        var el = document.getElementById(labelId);
        if (el) el.innerText = value.toUpperCase();
    }
</script>
