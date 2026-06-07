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
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    border: 1px solid #e5e7eb;
    padding: 20px;
    width: 300px;
    font-family: sans-serif;
">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <strong style="font-size:15px; color:#1f2937;">🎨 Live Theme Preview</strong>
        <button
            onclick="document.getElementById('tp-widget').style.display='none'; document.getElementById('tp-btn').style.display='flex';"
            style="background:none; border:none; font-size:22px; cursor:pointer; color:#6b7280; line-height:1;"
        >&times;</button>
    </div>

    <p style="font-size:11px; color:#6b7280; margin:0 0 16px; line-height:1.5;">
        Pick colors to preview live. Save hex codes in
        <strong style="color:#374151;">Admin → Configuration → Design → Theme Colors</strong>.
    </p>

    <div style="display:flex; flex-direction:column; gap:10px;">

        <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Primary Brand</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <code id="tp-hex-primary" style="font-size:11px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.primary_color') ?? '#060C3B' }}</code>
                <input type="color" value="{{ core()->getConfigData('general.design.theme_colors.primary_color') ?? '#060C3B' }}"
                    oninput="tpUpdate('--theme-navyBlue', this.value, 'tp-hex-primary')"
                    style="width:32px; height:32px; border:none; padding:0; cursor:pointer; border-radius:4px;">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Button Background</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <code id="tp-hex-btn-bg" style="font-size:11px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.button_bg_color') ?? '#060C3B' }}</code>
                <input type="color" value="{{ core()->getConfigData('general.design.theme_colors.button_bg_color') ?? '#060C3B' }}"
                    oninput="tpUpdate('--theme-button-bg', this.value, 'tp-hex-btn-bg')"
                    style="width:32px; height:32px; border:none; padding:0; cursor:pointer; border-radius:4px;">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Button Text</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <code id="tp-hex-btn-text" style="font-size:11px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.button_text_color') ?? '#ffffff' }}</code>
                <input type="color" value="{{ core()->getConfigData('general.design.theme_colors.button_text_color') ?? '#ffffff' }}"
                    oninput="tpUpdate('--theme-button-text', this.value, 'tp-hex-btn-text')"
                    style="width:32px; height:32px; border:none; padding:0; cursor:pointer; border-radius:4px;">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Nav Text</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <code id="tp-hex-nav-text" style="font-size:11px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.nav_text_color') ?? '#060C3B' }}</code>
                <input type="color" value="{{ core()->getConfigData('general.design.theme_colors.nav_text_color') ?? '#060C3B' }}"
                    oninput="tpUpdate('--theme-nav-text', this.value, 'tp-hex-nav-text')"
                    style="width:32px; height:32px; border:none; padding:0; cursor:pointer; border-radius:4px;">
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; background:#f9fafb; padding:8px 10px; border-radius:8px;">
            <label style="font-size:13px; font-weight:500; color:#374151;">Nav Border</label>
            <div style="display:flex; align-items:center; gap:8px;">
                <code id="tp-hex-nav-border" style="font-size:11px; color:#6b7280;">{{ core()->getConfigData('general.design.theme_colors.nav_border_color') ?? '#060C3B' }}</code>
                <input type="color" value="{{ core()->getConfigData('general.design.theme_colors.nav_border_color') ?? '#060C3B' }}"
                    oninput="tpUpdate('--theme-nav-border', this.value, 'tp-hex-nav-border')"
                    style="width:32px; height:32px; border:none; padding:0; cursor:pointer; border-radius:4px;">
            </div>
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
        font-size: 26px;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(79,70,229,0.5);
        align-items: center;
        justify-content: center;
        transition: transform 0.2s;
    "
    onmouseover="this.style.transform='scale(1.1)'"
    onmouseout="this.style.transform='scale(1)'"
    title="Open Live Theme Previewer"
>🎨</button>

<script>
    // Activate previewer when ?theme_preview is in the URL
    (function () {
        if (new URLSearchParams(window.location.search).has('theme_preview')) {
            document.getElementById('tp-btn').style.display = 'flex';
        }
    })();

    function tpUpdate(variable, value, labelId) {
        document.documentElement.style.setProperty(variable, value);
        var el = document.getElementById(labelId);
        if (el) el.innerText = value.toUpperCase();
    }
</script>
