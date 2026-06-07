<v-static-content :errors="errors">
    <x-admin::shimmer.settings.themes.static-content />
</v-static-content>

<!-- Static Content Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-static-content-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-2.5 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.static-content')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.static-content-description')
                        </p>
                    </div>

                    <div
                        class="flex gap-2.5"
                        v-if="isHtmlEditorActive"
                    >
                        <!-- Hidden Input Filed for upload images -->
                        <label
                            class="secondary-button"
                            for="static_image"
                        >
                            @lang('admin::app.settings.themes.edit.add-image-btn')
                        </label>

                        <input 
                            type="file"
                            name="static_image"
                            id="static_image"
                            class="hidden"
                            accept="image/*"
                            ref="static_image"
                            label="Image"
                            @change="storeImage($event)"
                        >
                    </div>
                </div>
                
                <div class="pt-4 text-center text-sm font-medium text-gray-500">
                    <div class="tabs">
                        <div class="mb-4 flex gap-4 border-b-2 pt-2 max-sm:hidden">
                            <!-- HTML Tab Header -->
                            <p @click="switchEditor('v-html-editor-theme', 1)">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': inittialEditor == 'v-html-editor-theme'}"
                                >
                                    @lang('admin::app.settings.themes.edit.html')
                                </div>
                            </p>

                            <!-- CSS Tab Editor -->
                            <p @click="switchEditor('v-css-editor-theme', 0);">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': inittialEditor == 'v-css-editor-theme'}"
                                >
                                    @lang('admin::app.settings.themes.edit.css')
                                </div>
                            </p>

                            <!-- Images Tab -->
                            <p @click="switchEditor('v-image-manager', 0);">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': inittialEditor == 'v-image-manager'}"
                                >
                                    Images (@{{ uploadedImages.length }})
                                </div>
                            </p>

                            <!-- Preview Tab Editor -->
                            <p @click="switchEditor('v-static-content-previewer', 0);">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': inittialEditor == 'v-static-content-previewer'}"
                                >
                                    @lang('admin::app.settings.themes.edit.preview')
                                </div>
                            </p>
                        </div>
                    </div>
                </div>

                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][html]"
                    v-model="options.html"
                />

                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][css]"
                    v-model="options.css"
                />

                <KeepAlive class="[&>*]:dark:bg-gray-900 [&>*]:dark:!text-white">
                    <component 
                        :is="inittialEditor"
                        ref="editor"
                        @editor-data="editorData"
                        :options="options"
                    >
                    </component>
                </KeepAlive>
            </div>
        </div>
    </script>

    <!-- Html Editor Template -->
    <script
        type="text/x-template"
        id="v-html-editor-theme-template"
    >
        <div ref="html"></div>
    </script>

    <!-- Css Editor Template -->
    <script
        type="text/x-template"
        id="v-css-editor-theme-template"
    >
        <div ref="css"></div>
    </script>

    <!-- Static Content Previewer -->
    <script
        type="text/x-template"
        id="v-static-content-previewer-template"
    >
        <div v-html="getPreviewContent()"></div>
    </script>

    <!-- Image Manager Template -->
    <script
        type="text/x-template"
        id="v-image-manager-template"
    >
        <div class="p-4">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        Manage Theme Images
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Upload, replace, or delete images. Click "Insert" to add to HTML editor.
                    </p>
                </div>
                <label class="secondary-button cursor-pointer">
                    + Upload New Image
                    <input
                        type="file"
                        class="hidden"
                        accept="image/*"
                        @change="uploadNewImage($event)"
                    />
                </label>
            </div>

            <div v-if="uploadedImages.length === 0" class="text-center py-12 text-gray-500">
                <span class="icon-image text-6xl text-gray-300 mb-4 block"></span>
                <p class="text-lg">No images uploaded yet</p>
                <p class="text-sm">Upload images to use in your theme</p>
            </div>

            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div
                    v-for="(image, index) in uploadedImages"
                    :key="'img-' + index"
                    class="relative border rounded-lg overflow-hidden hover:shadow-lg transition-shadow group"
                >
                    <img
                        :src="image.url"
                        class="w-full h-48 object-cover"
                        :alt="'Image ' + (index + 1)"
                    />
                    
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-opacity flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                        <button
                            type="button"
                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                            @click="insertImageToHTML(image.url)"
                            title="Insert to HTML"
                        >
                            Insert
                        </button>
                        <button
                            type="button"
                            class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                            @click="copyImageUrl(image.url)"
                            title="Copy URL"
                        >
                            Copy
                        </button>
                        <label
                            class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 cursor-pointer"
                            title="Replace"
                        >
                            Replace
                            <input
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="replaceImage(index, $event)"
                            />
                        </label>
                        <button
                            type="button"
                            class="px-3 py-1.5 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                            @click="deleteImage(index)"
                            title="Delete"
                        >
                            Delete
                        </button>
                    </div>
                    
                    <div class="p-2 bg-gray-50 dark:bg-gray-800">
                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate" :title="image.url">
                            @{{ image.url.split('/').pop() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-static-content', {
            template: '#v-static-content-template',

            props: ['errors'],

            data() {
                return {
                    inittialEditor: 'v-html-editor-theme',

                    options: @json($theme->translate($currentLocale->code)['options'] ?? null),

                    isHtmlEditorActive: true,

                    uploadedImages: [],
                };
            },

            created() {
                if (this.options === null) {
                    this.options = { html: {} };
                }
                
                this.extractUploadedImages();
            },

            mounted() {
                this.applydarkColor();
            },

            methods: {
                switchEditor(editor, isActive) {
                    this.inittialEditor = editor;
                    this.isHtmlEditorActive = isActive;

                    this.$nextTick(() => {
                        this.applydarkColor();

                        if (editor == 'v-static-content-previewer') {
                            this.$refs.editor.review = this.options;
                        }
                    });
                },

                editorData(value) {
                    if (value.html) {
                        this.options.html = value.html;
                        this.extractUploadedImages();
                    } else {
                        this.options.css = value.css;
                    }
                },

                storeImage($event) {
                    let imageInput = this.$refs.static_image;

                    if (imageInput.files == undefined) {
                        return;
                    }

                    const validFiles = Array.from(imageInput.files).every(file => file.type.includes('image/'));

                    if (!validFiles) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: '@lang('admin::app.settings.themes.edit.image-upload-message')'
                        });

                        imageInput.value = '';
                        return;
                    }

                    imageInput.files.forEach((file, index) => {
                        this.$refs.editor.storeImage($event);
                    });
                },

                extractUploadedImages() {
                    const html = this.options.html || '';
                    const imgRegex = /(?:src|data-src)="([^"]*storage\/theme[^"]*)"/gi;
                    const matches = [...html.matchAll(imgRegex)];
                    
                    this.uploadedImages = matches
                        .map(match => ({
                            url: match[1].startsWith('http') ? match[1] : '{{ config("app.url") }}/' + match[1].replace(/^\//, '')
                        }))
                        .filter((img, index, self) => 
                            index === self.findIndex(t => t.url === img.url)
                        );
                },

                applydarkColor() {
                    this.$nextTick(() => {
                        const codeMirrorGutters = this.$el.querySelector('.CodeMirror-gutters');
                        if (codeMirrorGutters) {
                            codeMirrorGutters.classList.add('dark:bg-gray-900', 'dark:!text-white');
                        }
                    });
                },
            },
        });
    </script>

    <!-- Html Editor Component -->
    <script type="module">
        app.component('v-html-editor-theme', {
            template: '#v-html-editor-theme-template',
            
            data() {
                return {
                    options:{
                        html: `{!! $theme->translate($currentLocale->code)['options']['html'] ?? '' !!}`,
                    },

                    cursorPointer: {},
                };
            },

            created() {
                this.initHtmlEditor();

                this.$emitter.on('change-theme', (theme) => this._html.setOption('theme', (theme === 'dark') ? 'ayu-dark' : 'default'));
            },

            methods: {
                initHtmlEditor() {
                    this.$nextTick(() => {
                        this.options.html = SimplyBeautiful().html(this.options.html);

                        this._html = new CodeMirror(this.$refs.html, {
                            lineNumbers: true,
                            tabSize: 4,
                            lineWrapping: true,
                            lineWiseCopyCut: true,
                            value: this.options.html,
                            mode: 'htmlmixed',
                            theme: document.documentElement.classList.contains('dark') ? 'ayu-dark' : 'default',
                        });

                        this._html.on('changes', (e) => {
                            this.options.html = this._html.getValue();

                            this.cursorPointer = e.getCursor();

                            this.$emit('editorData', this.options);
                        });
                    });
                },

                storeImage($event) {
                    let selectedImage = $event.target.files[0];

                    if (! selectedImage) {
                        return;
                    }

                    const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];

                    if (! allowedImageTypes.includes(selectedImage.type)) {
                        return;
                    }

                    let formData = new FormData();

                    formData.append('{{ $currentLocale->code }}[options][][image]', selectedImage);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            let editor = this._html.getDoc();

                            let cursorPointer = editor.getCursor();

                            editor.replaceRange(`<img class="lazy" src="" data-src="${response.data}">`, {
                                line: cursorPointer.line, ch: cursorPointer.ch
                            });

                            editor.setCursor({
                                line: cursorPointer.line, ch: cursorPointer.ch + response.data.length
                            });
                        })
                        .catch((error) => {
                            if (error.response.status == 422) {
                                this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.message });
                            }
                        });
                },
            },
        });
    </script>
    
    <!-- Css Editor Component -->
    <script type="module">
        app.component('v-css-editor-theme', {
            template: '#v-css-editor-theme-template',

            data() {
                return {
                    options:{
                        css: `{!! $theme->translate($currentLocale->code)['options']['css'] ?? '' !!}`,
                    }
                };
            },

            created() {
                this.initCssEditor();

                this.$emitter.on('change-theme', (theme) => this._css.setOption('theme', (theme === 'dark') ? 'ayu-dark' : 'default'));
            },

            methods: {
                initCssEditor() {
                    this.$nextTick(() => {
                        this.options.css = SimplyBeautiful().css(this.options.css);

                        this._css = new CodeMirror(this.$refs.css, {
                            lineNumbers: true,
                            lineWrapping: true,
                            tabSize: 4,
                            lineWiseCopyCut: true,
                            value: this.options.css,
                            mode: 'css',
                            theme: document.documentElement.classList.contains('dark') ? 'ayu-dark' : 'default',
                        });

                        this._css.on('changes', () => {
                            this.options.css = this._css.getValue();

                            this.$emit('editorData', this.options);
                        });
                    });
                },
            },
        });
    </script>
    
    <!-- Static Content Previewer -->
    <script type="module">
        app.component('v-static-content-previewer', {
            template: '#v-static-content-previewer-template',

            props: ['options'],

            methods: {
                getPreviewContent() {
                    let html = this.options.html.slice();

                    html = html.replaceAll('src=""', '').replaceAll('data-src', 'src').replaceAll('src="storage/theme/', "src=\"{{ config('app.url') }}/storage/theme/");

                    return html + '<style type=\"text/css\">' +   this.options.css + '</style>';
                },
            },
        });
    </script>

    <!-- Image Manager Component -->
    <script type="module">
        app.component('v-image-manager', {
            template: '#v-image-manager-template',

            props: ['options'],

            data() {
                return {
                    uploadedImages: [],
                };
            },

            created() {
                this.extractUploadedImages();
            },

            methods: {
                extractUploadedImages() {
                    const html = this.options.html || '';
                    const imgRegex = /(?:src|data-src)="([^"]*storage\/theme[^"]*)"/gi;
                    const matches = [...html.matchAll(imgRegex)];
                    
                    this.uploadedImages = matches
                        .map(match => ({
                            url: match[1].startsWith('http') ? match[1] : '{{ config("app.url") }}/' + match[1].replace(/^\//, '')
                        }))
                        .filter((img, index, self) => 
                            index === self.findIndex(t => t.url === img.url)
                        );
                },

                uploadNewImage(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg', 'image/gif'];

                    if (!allowedImageTypes.includes(file.type)) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: 'Please select a valid image file (JPEG, PNG, WebP, GIF)'
                        });
                        return;
                    }

                    let formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', file);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            const newUrl = '{{ config("app.url") }}/' + response.data;
                            this.uploadedImages.push({ url: newUrl });
                            
                            // Auto-insert into HTML
                            const relativeUrl = response.data;
                            const imgTag = `<img class="lazy" src="" data-src="${relativeUrl}">`;
                            
                            // Try to insert into HTML editor
                            this.$parent.$nextTick(() => {
                                if (this.$parent.$refs.editor && this.$parent.$refs.editor._html) {
                                    let editor = this.$parent.$refs.editor._html.getDoc();
                                    let cursorPointer = editor.getCursor();
                                    
                                    editor.replaceRange('\n' + imgTag + '\n', {
                                        line: cursorPointer.line, 
                                        ch: cursorPointer.ch
                                    });
                                    
                                    // Update parent's HTML
                                    this.$parent.options.html = editor.getValue();
                                    this.$parent.extractUploadedImages();
                                } else {
                                    // If editor is not rendered, just update the HTML directly
                                    this.$parent.options.html += '\n' + imgTag + '\n';
                                    this.$parent.extractUploadedImages();
                                }
                            });
                            
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: 'Image uploaded and inserted into HTML! Check Preview tab to see it.'
                            });
                            
                            event.target.value = '';
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Image upload failed'
                            });
                        });
                },

                insertImageToHTML(url) {
                    const relativeUrl = url.replace('{{ config("app.url") }}/', '');
                    const imgTag = `<img class="lazy" src="" data-src="${relativeUrl}">`;
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(imgTag);
                    
                    this.$emitter.emit('add-flash', {
                        type: 'success',
                        message: 'Image tag copied! Switch to HTML tab and paste (Ctrl+V) where you want it.'
                    });
                    
                    // Also try to insert directly if parent has the editor
                    this.$parent.$nextTick(() => {
                        if (this.$parent.$refs.editor && this.$parent.$refs.editor._html) {
                            let editor = this.$parent.$refs.editor._html.getDoc();
                            let cursorPointer = editor.getCursor();
                            
                            editor.replaceRange('\n' + imgTag + '\n', {
                                line: cursorPointer.line, 
                                ch: cursorPointer.ch
                            });
                            
                            // Update parent's HTML
                            this.$parent.options.html = editor.getValue();
                            
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: 'Image inserted into HTML! Check HTML tab or Preview tab.'
                            });
                        } else {
                            this.$parent.options.html += '\n' + imgTag + '\n';
                        }
                    });
                },

                copyImageUrl(url) {
                    navigator.clipboard.writeText(url).then(() => {
                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: 'Image URL copied to clipboard!'
                        });
                    });
                },

                replaceImage(index, event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    let formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', file);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            const oldFullUrl = this.uploadedImages[index].url;
                            const newRelativeUrl = response.data;
                            const newFullUrl = '{{ config("app.url") }}/' + newRelativeUrl.replace(/^\//, '');

                            // Strip domain to get the bare relative path (e.g. storage/theme/1/abc.webp)
                            const oldRelative = oldFullUrl
                                .replace(/^https?:\/\/[^\/]+\/?/, '')
                                .replace(/^\//, '');

                            // Replace any occurrence of the old URL in HTML (with or without leading slash)
                            let html = this.$parent.options.html;
                            html = html.replace(new RegExp('\\/' + oldRelative.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), '/' + newRelativeUrl.replace(/^\//, ''));
                            html = html.replace(new RegExp(oldRelative.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), newRelativeUrl.replace(/^\//, ''));
                            this.$parent.options.html = html;

                            // Sync editor
                            this.$parent.$nextTick(() => {
                                if (this.$parent.$refs.editor && this.$parent.$refs.editor._html) {
                                    this.$parent.$refs.editor._html.setValue(this.$parent.options.html);
                                }
                                this.$parent.extractUploadedImages();
                            });

                            this.uploadedImages[index].url = newFullUrl;

                            this.$emitter.emit('add-flash', { type: 'success', message: 'Image replaced!' });
                            event.target.value = '';
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Image replacement failed'
                            });
                        });
                },

                deleteImage(index) {
                    if (!confirm('Delete this image? It will be removed from the HTML.')) {
                        return;
                    }

                    const imageUrl = this.uploadedImages[index].url;

                    // Get bare relative path (e.g. storage/theme/1/abc.webp) from full URL
                    const bareRelative = imageUrl
                        .replace(/^https?:\/\/[^\/]+\/?/, '')
                        .replace(/^\//, '');

                    // Match img tags containing this path in data-src (with or without leading slash)
                    const escaped = bareRelative.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    let html = this.$parent.options.html;
                    html = html.replace(new RegExp(`<img[^>]*data-src="\\/?${escaped}"[^>]*>`, 'gi'), '');
                    // Also match if stored with full URL in data-src
                    const escapedFull = imageUrl.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    html = html.replace(new RegExp(`<img[^>]*data-src="${escapedFull}"[^>]*>`, 'gi'), '');
                    this.$parent.options.html = html;

                    // Sync editor
                    this.$parent.$nextTick(() => {
                        if (this.$parent.$refs.editor && this.$parent.$refs.editor._html) {
                            this.$parent.$refs.editor._html.setValue(this.$parent.options.html);
                        }
                        this.$parent.extractUploadedImages();
                    });

                    this.uploadedImages.splice(index, 1);

                    this.$emitter.emit('add-flash', { type: 'success', message: 'Image removed from HTML!' });
                },
            },
        });
    </script>

    <!-- Code mirror script CDN -->
    <script
        type="text/javascript"
        src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/codemirror.js"
    >
    </script>

    <!-- 
        Html mixed and xml cnd both are dependent 
        Used for html and css theme
    -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/mode/xml/xml.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/mode/htmlmixed/htmlmixed.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/mode/css/css.js"></script>

    <!-- Beatutify html and css -->
    <script src="https://cdn.jsdelivr.net/npm/simply-beautiful@latest/dist/index.min.js"></script>
@endPushOnce

@pushOnce('styles')
    <!-- Code mirror style cdn -->
    <link 
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.4/codemirror.css"
    >
    </link>

    <!-- Dark theme css -->
    <link rel="stylesheet" href="https://codemirror.net/5/theme/ayu-dark.css">
@endPushOnce