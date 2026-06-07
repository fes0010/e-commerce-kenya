{{--
    Theme Studio Widget
    Activate by visiting any page with ?theme_preview in the URL
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
    display: flex;
    flex-direction: column;
">
    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; padding:16px 18px; border-bottom:1px solid #f3f4f6; position:sticky; top:0; background:#fff; z-index:10; border-radius:14px 14px 0 0;">
        <strong style="font-size:16px; color:#1f2937;">🎨 Theme Studio</strong>
        <div style="display:flex; align-items:center; gap:10px;">
            <button id="tp-save-btn" onclick="tpSave()" style="background:#4f46e5; color:#fff; border:none; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; gap:4px; transition:background .2s;">
                <span id="tp-save-text">Save to Live</span>
                <span id="tp-save-spinner" style="display:none;">⏳</span>
            </button>
            <button onclick="document.getElementById('tp-widget').style.display='none'; document.getElementById('tp-btn').style.display='flex';" style="background:none; border:none; font-size:22px; cursor:pointer; color:#6b7280; line-height:1; padding:0 4px;">&times;</button>
        </div>
    </div>

    {{-- Tabs --}}
    <div style="display:flex; border-bottom:1px solid #e5e7eb; background:#f9fafb; position:sticky; top:58px; z-index:9;">
        <button id="tp-tab-colors" onclick="tpSwitchTab('colors')" style="flex:1; padding:12px 0; border:none; background:none; font-size:13px; font-weight:600; color:#4f46e5; border-bottom:2px solid #4f46e5; cursor:pointer;">Colors & Fonts</button>
        <button id="tp-tab-layout" onclick="tpSwitchTab('layout')" style="flex:1; padding:12px 0; border:none; background:none; font-size:13px; font-weight:600; color:#6b7280; border-bottom:2px solid transparent; cursor:pointer;">Layout Editor</button>
    </div>

    <div style="padding: 16px 18px 18px; flex:1; overflow-y:auto; position:relative;">

        {{-- ===== TAB: Colors & Fonts ===== --}}
        <div id="tp-content-colors" style="display:block;">
            {{-- Pre-built Themes --}}
            <div style="margin-bottom:18px;">
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin:0 0 10px;">Quick Themes</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    @php
                        $presets = [
                            'default' => ['name' => 'Default', 'desc' => 'Poppins · Navy', 'colors' => ['#060C3B','#F6F2EB','#40994A']],
                            'sunset' => ['name' => 'Sunset', 'desc' => 'Outfit · Deep Plum', 'colors' => ['#2D142C','#FEECE9','#E23E57']],
                            'forest' => ['name' => 'Forest', 'desc' => 'Inter · Deep Green', 'colors' => ['#132A13','#ECF39E','#4F772D']],
                            'ocean' => ['name' => 'Ocean', 'desc' => 'Roboto · Deep Teal', 'colors' => ['#0F2027','#E8F1F2','#2A9D8F']],
                            'red' => ['name' => 'Red', 'desc' => 'Poppins · Crimson', 'colors' => ['#8B0000','#FFEEEE','#DC143C']],
                            'maroon' => ['name' => 'Maroon', 'desc' => 'Inter · Dark Maroon', 'colors' => ['#4A0404','#FDF5F5','#C2185B']],
                        ];
                    @endphp
                    @foreach($presets as $key => $preset)
                    <button onclick="tpApplyTheme('{{ $key }}')" style="border:2px solid #e5e7eb; border-radius:10px; padding:10px 8px; cursor:pointer; background:#fff; text-align:left; transition:border-color .15s;" onmouseover="this.style.borderColor='#6366f1'" onmouseout="this.style.borderColor='#e5e7eb'">
                        <div style="display:flex; gap:4px; margin-bottom:6px;">
                            <span style="width:18px; height:18px; border-radius:50%; background:{{ $preset['colors'][0] }};"></span>
                            <span style="width:18px; height:18px; border-radius:50%; background:{{ $preset['colors'][1] }}; border:1px solid #e5e7eb"></span>
                            <span style="width:18px; height:18px; border-radius:50%; background:{{ $preset['colors'][2] }};"></span>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#374151;">{{ $preset['name'] }}</span><br>
                        <span style="font-size:10px; color:#9ca3af;">{{ $preset['desc'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            <div style="height:1px; background:#f3f4f6; margin:0 0 16px;"></div>

            {{-- Body Font --}}
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

            {{-- Individual Colors --}}
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
                        <input type="color" data-theme-key="{{ $field['key'] }}"
                            value="{{ core()->getConfigData('general.design.theme_colors.'.$field['key']) ?? $field['default'] }}"
                            oninput="tpColor('{{ $field['var'] }}', this.value, '{{ $field['id'] }}')"
                            style="width:30px; height:30px; border:none; padding:0; cursor:pointer; border-radius:4px; background:none;">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ===== TAB: Layout Editor ===== --}}
        <div id="tp-content-layout" style="display:none;">
            <p style="font-size:11px; color:#6b7280; margin:0 0 12px; line-height:1.5;">
                Drag sections to reorder them on the homepage. Click the eye icon to show/hide.
            </p>

            <div id="tp-layout-loading" style="text-align:center; padding:20px; font-size:13px; color:#6b7280;">
                Loading layout...
            </div>

            <div id="tp-layout-list" style="display:flex; flex-direction:column; gap:8px;">
                {{-- Injected via JS --}}
            </div>
        </div>

    </div>
</div>

{{-- Floating toggle button --}}
<button id="tp-btn"
    onclick="document.getElementById('tp-widget').style.display='flex'; this.style.display='none'; tpLoadLayout();"
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
    var currentPreset = "{{ core()->getConfigData('general.design.theme_colors.theme_preset') ?? 'default' }}";
    var currentFont = "{{ core()->getConfigData('general.design.theme_colors.body_font') ?? 'Poppins' }}";
    var layoutData = [];

    // Pre-built themes
    var TP_THEMES = {
        'default': { '--theme-navyBlue': '#060C3B', '--theme-lightOrange': '#F6F2EB', '--theme-darkGreen': '#40994A', '--theme-darkBlue': '#0044F2', '--theme-darkPink': '#F85156', 'font': 'Poppins' },
        'sunset':  { '--theme-navyBlue': '#2D142C', '--theme-lightOrange': '#FEECE9', '--theme-darkGreen': '#3E8E7E', '--theme-darkBlue': '#51C4D3', '--theme-darkPink': '#E23E57', 'font': 'Outfit' },
        'forest':  { '--theme-navyBlue': '#132A13', '--theme-lightOrange': '#ECF39E', '--theme-darkGreen': '#4F772D', '--theme-darkBlue': '#31572C', '--theme-darkPink': '#90A955', 'font': 'Inter' },
        'ocean':   { '--theme-navyBlue': '#0F2027', '--theme-lightOrange': '#E8F1F2', '--theme-darkGreen': '#2A9D8F', '--theme-darkBlue': '#203A43', '--theme-darkPink': '#2C5364', 'font': 'Roboto' },
        'red':     { '--theme-navyBlue': '#8B0000', '--theme-lightOrange': '#FFEEEE', '--theme-darkGreen': '#228B22', '--theme-darkBlue': '#B22222', '--theme-darkPink': '#DC143C', 'font': 'Poppins' },
        'maroon':  { '--theme-navyBlue': '#4A0404', '--theme-lightOrange': '#FDF5F5', '--theme-darkGreen': '#2E5C2E', '--theme-darkBlue': '#800000', '--theme-darkPink': '#C2185B', 'font': 'Inter' },
    };

    // Initialize
    (function () {
        if (new URLSearchParams(window.location.search).has('theme_preview')) {
            document.getElementById('tp-btn').style.display = 'flex';
        }
    })();

    // Tabs
    function tpSwitchTab(tab) {
        if(tab === 'colors') {
            document.getElementById('tp-content-colors').style.display = 'block';
            document.getElementById('tp-content-layout').style.display = 'none';
            document.getElementById('tp-tab-colors').style.color = '#4f46e5';
            document.getElementById('tp-tab-colors').style.borderBottomColor = '#4f46e5';
            document.getElementById('tp-tab-layout').style.color = '#6b7280';
            document.getElementById('tp-tab-layout').style.borderBottomColor = 'transparent';
        } else {
            document.getElementById('tp-content-colors').style.display = 'none';
            document.getElementById('tp-content-layout').style.display = 'block';
            document.getElementById('tp-tab-colors').style.color = '#6b7280';
            document.getElementById('tp-tab-colors').style.borderBottomColor = 'transparent';
            document.getElementById('tp-tab-layout').style.color = '#4f46e5';
            document.getElementById('tp-tab-layout').style.borderBottomColor = '#4f46e5';
            tpLoadLayout(); // load if empty
        }
    }

    // Color/Font updates
    function tpApplyTheme(name) {
        currentPreset = name;
        var t = TP_THEMES[name];
        if (!t) return;
        var root = document.documentElement;
        Object.keys(t).forEach(function(k) { if (k !== 'font') root.style.setProperty(k, t[k]); });
        root.style.setProperty('--theme-button-bg',   t['--theme-navyBlue']);
        root.style.setProperty('--theme-nav-text',    t['--theme-navyBlue']);
        root.style.setProperty('--theme-nav-border',  t['--theme-navyBlue']);
        if (t.font) tpSetFont(t.font);

        var map = {
            'tp-primary': '--theme-navyBlue', 'tp-bg': '--theme-lightOrange', 'tp-accent': '--theme-darkGreen',
            'tp-link': '--theme-darkBlue', 'tp-danger': '--theme-darkPink', 'tp-btn-bg': '--theme-navyBlue',
            'tp-nav-text': '--theme-navyBlue', 'tp-nav-border': '--theme-navyBlue'
        };
        Object.keys(map).forEach(function(id) {
            var val = t[map[id]];
            var el = document.getElementById(id);
            if (el && val) {
                el.innerText = val.toUpperCase();
                el.nextElementSibling.value = val;
            }
        });
    }

    function tpSetFont(fontName) {
        currentFont = fontName;
        currentPreset = 'custom';
        var linkId = 'tp-font-' + fontName.replace(/\s/g,'');
        if (!document.getElementById(linkId)) {
            var link = document.createElement('link'); link.id = linkId; link.rel = 'stylesheet';
            link.href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent(fontName) + ':wght@400;500;600;700&display=swap';
            document.head.appendChild(link);
        }
        document.documentElement.style.setProperty('--theme-font-poppins', '"' + fontName + '"');
        document.body.style.fontFamily = '"' + fontName + '", sans-serif';
    }

    function tpColor(variable, value, labelId) {
        currentPreset = 'custom';
        document.documentElement.style.setProperty(variable, value);
        var el = document.getElementById(labelId);
        if (el) el.innerText = value.toUpperCase();
    }

    // Layout Editor
    function tpLoadLayout() {
        if(layoutData.length > 0) return; // Already loaded
        
        // Use native fetch to avoid depending on axios/vue specifically
        fetch("{{ route('shop.api.theme_studio.layout') }}")
            .then(res => res.json())
            .then(res => {
                layoutData = res.data || [];
                tpRenderLayout();
                document.getElementById('tp-layout-loading').style.display = 'none';
            })
            .catch(e => {
                document.getElementById('tp-layout-loading').innerText = 'Error loading layout. Are you on the homepage?';
            });
    }

    function tpRenderLayout() {
        var list = document.getElementById('tp-layout-list');
        list.innerHTML = '';
        
        layoutData.sort((a,b) => a.sort_order - b.sort_order).forEach((item, index) => {
            var div = document.createElement('div');
            div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; background:#fff; border:1px solid #e5e7eb; padding:10px 12px; border-radius:8px;';
            if(!item.status) div.style.opacity = '0.6';
            
            var nameMap = {
                'image_carousel': 'Image Slider',
                'category_carousel': 'Category Slider',
                'product_carousel': 'Product Slider',
                'static_content': 'Banner / Text',
            };
            var friendlyType = nameMap[item.type] || item.type;
            
            var left = document.createElement('div');
            left.style.cssText = 'display:flex; flex-direction:column; gap:2px;';
            left.innerHTML = '<span style="font-size:13px; font-weight:600; color:#374151;">' + item.name + '</span>' + 
                             '<span style="font-size:10px; color:#9ca3af; text-transform:uppercase;">' + friendlyType + '</span>';
            
            var right = document.createElement('div');
            right.style.cssText = 'display:flex; align-items:center; gap:4px;';
            
            // Visibility toggle
            var visBtn = document.createElement('button');
            visBtn.innerHTML = item.status ? '👁️' : '🚫';
            visBtn.title = item.status ? 'Hide section' : 'Show section';
            visBtn.style.cssText = 'background:none; border:none; cursor:pointer; font-size:16px; padding:4px; border-radius:4px;';
            visBtn.onmouseover = () => visBtn.style.background = '#f3f4f6';
            visBtn.onmouseout = () => visBtn.style.background = 'none';
            visBtn.onclick = () => {
                item.status = item.status ? 0 : 1;
                tpUpdateDomVisibility(item.id, item.status);
                tpRenderLayout();
            };
            
            // Move up
            var upBtn = document.createElement('button');
            upBtn.innerHTML = '↑';
            upBtn.disabled = index === 0;
            upBtn.style.cssText = 'background:none; border:none; cursor:pointer; font-size:16px; font-weight:bold; color:' + (index===0 ? '#d1d5db' : '#4b5563') + '; padding:4px; border-radius:4px;';
            if(!upBtn.disabled) {
                upBtn.onmouseover = () => upBtn.style.background = '#f3f4f6';
                upBtn.onmouseout = () => upBtn.style.background = 'none';
                upBtn.onclick = () => {
                    var temp = layoutData[index].sort_order;
                    layoutData[index].sort_order = layoutData[index-1].sort_order;
                    layoutData[index-1].sort_order = temp;
                    tpReorderDom();
                    tpRenderLayout();
                };
            }
            
            // Move down
            var dnBtn = document.createElement('button');
            dnBtn.innerHTML = '↓';
            dnBtn.disabled = index === layoutData.length - 1;
            dnBtn.style.cssText = 'background:none; border:none; cursor:pointer; font-size:16px; font-weight:bold; color:' + (index===layoutData.length-1 ? '#d1d5db' : '#4b5563') + '; padding:4px; border-radius:4px;';
            if(!dnBtn.disabled) {
                dnBtn.onmouseover = () => dnBtn.style.background = '#f3f4f6';
                dnBtn.onmouseout = () => dnBtn.style.background = 'none';
                dnBtn.onclick = () => {
                    var temp = layoutData[index].sort_order;
                    layoutData[index].sort_order = layoutData[index+1].sort_order;
                    layoutData[index+1].sort_order = temp;
                    tpReorderDom();
                    tpRenderLayout();
                };
            }
            
            right.appendChild(visBtn);
            right.appendChild(upBtn);
            right.appendChild(dnBtn);
            
            div.appendChild(left);
            div.appendChild(right);
            list.appendChild(div);
        });
    }

    function tpUpdateDomVisibility(id, status) {
        var el = document.querySelector('[data-theme-section="' + id + '"]');
        if(el) {
            if(status) {
                el.style.display = '';
                el.style.opacity = '1';
            } else {
                el.style.display = 'none';
                el.style.opacity = '0';
            }
        }
    }

    function tpReorderDom() {
        var container = document.getElementById('theme-layout-container');
        if(!container) return;
        
        var sortedIds = layoutData.slice().sort((a,b) => a.sort_order - b.sort_order).map(i => i.id);
        
        sortedIds.forEach(id => {
            var el = document.querySelector('[data-theme-section="' + id + '"]');
            if(el) container.appendChild(el);
        });
    }

    // Save functionality
    function tpSave() {
        var btn = document.getElementById('tp-save-btn');
        var text = document.getElementById('tp-save-text');
        var spin = document.getElementById('tp-save-spinner');
        
        btn.style.background = '#6b7280';
        text.innerText = 'Saving...';
        spin.style.display = 'inline';
        
        // Collect colors
        var colorsData = {
            theme_preset: currentPreset,
            body_font: currentFont
        };
        
        document.querySelectorAll('input[data-theme-key]').forEach(function(input) {
            colorsData[input.getAttribute('data-theme-key')] = input.value;
        });

        var payload = {
            _token: "{{ csrf_token() }}",
            colors: colorsData,
            layout: layoutData
        };

        fetch("{{ route('shop.api.theme_studio.save') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => {
            if(res.status === 401) throw new Error('Unauthorized! Please log into the Admin Dashboard in another tab.');
            return res.json();
        })
        .then(res => {
            btn.style.background = '#10b981'; // Green success
            text.innerText = 'Saved!';
            spin.style.display = 'none';
            setTimeout(() => {
                btn.style.background = '#4f46e5';
                text.innerText = 'Save to Live';
            }, 3000);
        })
        .catch(err => {
            btn.style.background = '#ef4444'; // Red error
            text.innerText = 'Error';
            spin.style.display = 'none';
            alert(err.message || 'Error saving layout.');
            setTimeout(() => {
                btn.style.background = '#4f46e5';
                text.innerText = 'Save to Live';
            }, 3000);
        });
    }
</script>
