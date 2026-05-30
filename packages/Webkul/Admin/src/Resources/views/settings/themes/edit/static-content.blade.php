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
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.static-content')
                        </p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.static-content-description')
                        </p>
                    </div>

                    <!-- Mode Toggle -->
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="secondary-button"
                            :class="{'primary-button': mode === 'visual'}"
                            @click="mode = 'visual'"
                        >
                            <span class="icon-image"></span>
                            @lang('admin::app.settings.themes.edit.images')
                        </button>
                        <button
                            type="button"
                            class="secondary-button"
                            :class="{'primary-button': mode === 'code'}"
                            @click="mode = 'code'"
                        >
                            <span class="icon-code"></span>
                            @lang('admin::app.settings.themes.edit.html')
                        </button>
                    </div>
                </div>

                <!-- Hidden inputs for form submission -->
                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][html]"
                    :value="mode === 'code' ? htmlCode : ''"
                />
                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][css]"
                    :value="mode === 'code' ? cssCode : ''"
                />
                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][layout]"
                    :value="layout"
                />
                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][text]"
                    :value="textContent"
                />

                <!-- Visual Editor Mode -->
                <div v-show="mode === 'visual'" class="space-y-4">
                    <!-- Image Gallery Section -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.settings.themes.edit.image-gallery')
                            </h3>
                            <label
                                class="secondary-button cursor-pointer"
                                for="static-content-new-image"
                            >
                                <span class="icon-add"></span>
                                @lang('admin::app.settings.themes.edit.add-image-btn')
                            </label>
                            <input
                                type="file"
                                id="static-content-new-image"
                                class="hidden"
                                accept="image/*"
                                multiple
                                @change="addImagesFromFile($event)"
                            />
                        </div>

                        <!-- Images Grid -->
                        <div
                            v-if="images.length"
                            class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4"
                        >
                            <div
                                v-for="(image, index) in images"
                                :key="index"
                                class="group relative overflow-hidden rounded-lg border-2 transition-all"
                                :class="dragIndex === index ? 'border-blue-500 opacity-50' : 'border-gray-200 hover:border-blue-400 dark:border-gray-700'"
                                draggable="true"
                                @dragstart="onDragStart(index, $event)"
                                @dragover.prevent="onDragOver(index, $event)"
                                @drop="onDrop(index)"
                                @dragend="onDragEnd"
                            >
                                <!-- Drag Handle -->
                                <div class="absolute left-2 top-2 z-10 flex h-6 w-6 cursor-grab items-center justify-center rounded-full bg-black/50 text-white opacity-0 transition-opacity group-hover:opacity-100">
                                    <span class="icon-sort-down text-xs"></span>
                                </div>

                                <!-- Position Badge -->
                                <div class="absolute right-2 top-2 z-10 flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">
                                    @{{ index + 1 }}
                                </div>

                                <!-- Image Preview -->
                                <div class="aspect-square bg-gray-50 dark:bg-gray-800">
                                    <img
                                        v-if="image.url"
                                        :src="image.url"
                                        class="h-full w-full object-cover"
                                        :alt="image.alt || 'Image'"
                                    />
                                    <div v-else class="flex h-full items-center justify-center">
                                        <span class="icon-image text-4xl text-gray-300"></span>
                                    </div>
                                </div>

                                <!-- Image Info -->
                                <div class="space-y-1.5 p-2">
                                    <input
                                        type="text"
                                        v-model="image.alt"
                                        placeholder="@lang('admin::app.settings.themes.edit.image-alt-placeholder')"
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <input
                                        type="text"
                                        v-model="image.link"
                                        placeholder="@lang('admin::app.settings.themes.edit.image-link-placeholder')"
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <input
                                        type="text"
                                        v-model="image.caption"
                                        placeholder="@lang('admin::app.settings.themes.edit.image-caption-placeholder')"
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                    />
                                </div>

                                <!-- Actions Overlay -->
                                <div class="absolute bottom-2 right-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <label
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white/90 text-gray-700 shadow-lg hover:bg-white"
                                        :for="'static-content-replace-' + index"
                                    >
                                        <span class="icon-edit text-sm"></span>
                                    </label>
                                    <input
                                        type="file"
                                        :id="'static-content-replace-' + index"
                                        class="hidden"
                                        accept="image/*"
                                        @change="replaceImage(index, $event)"
                                    />
                                    <button
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500/90 text-white shadow-lg hover:bg-red-600"
                                        @click="removeImage(index)"
                                    >
                                        <span class="icon-delete text-sm"></span>
                                    </button>
                                </div>

                                <!-- Hidden inputs for form submission -->
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][images][' + index + '][url]'"
                                    :value="image.url"
                                />
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][images][' + index + '][alt]'"
                                    :value="image.alt"
                                />
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][images][' + index + '][link]'"
                                    :value="image.link"
                                />
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][images][' + index + '][caption]'"
                                    :value="image.caption"
                                />
                                <input
                                    type="hidden"
                                    :name="'{{ $currentLocale->code }}[options][images][' + index + '][position]'"
                                    :value="index"
                                />
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-else
                            class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12 dark:border-gray-700"
                        >
                            <span class="icon-image text-5xl text-gray-300"></span>
                            <p class="mt-3 text-sm text-gray-500">
                                @lang('admin::app.settings.themes.edit.no-images')
                            </p>
                            <label
                                class="primary-button mt-3 cursor-pointer"
                                for="static-content-new-image"
                            >
                                @lang('admin::app.settings.themes.edit.add-first-image')
                            </label>
                        </div>
                    </div>

                    <!-- Text Content Section -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.text-content')
                        </h3>
                        <textarea
                            v-model="textContent"
                            rows="4"
                            class="w-full rounded border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            :placeholder="@json(__('admin::app.settings.themes.edit.text-content-placeholder'))"
                        ></textarea>
                    </div>

                    <!-- Layout Options -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.layout-options')
                        </h3>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <label
                                v-for="opt in layoutOptions"
                                :key="opt.value"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border-2 p-3 transition-all"
                                :class="layout === opt.value ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 hover:border-gray-300 dark:border-gray-700'"
                            >
                                <input
                                    type="radio"
                                    v-model="layout"
                                    :value="opt.value"
                                    class="hidden"
                                />
                                <span :class="layout === opt.value ? 'icon-check-circle text-blue-600' : 'icon-radio-empty text-gray-400'"></span>
                                <span class="text-sm">@{{ opt.label }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.preview')
                        </h3>
                        <div class="rounded bg-gray-50 p-4 dark:bg-gray-800" v-html="getPreview()"></div>
                    </div>
                </div>

                <!-- Code Editor Mode (Legacy) -->
                <div v-show="mode === 'code'">
                    <div class="mb-4 flex gap-4 border-b-2">
                        <button
                            type="button"
                            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                            :class="{'-mb-px border-b-2 border-blue-600': codeTab === 'html'}"
                            @click="codeTab = 'html'"
                        >
                            @lang('admin::app.settings.themes.edit.html')
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                            :class="{'-mb-px border-b-2 border-blue-600': codeTab === 'css'}"
                            @click="codeTab = 'css'"
                        >
                            @lang('admin::app.settings.themes.edit.css')
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                            :class="{'-mb-px border-b-2 border-blue-600': codeTab === 'preview'}"
                            @click="codeTab = 'preview'"
                        >
                            @lang('admin::app.settings.themes.edit.preview')
                        </button>
                    </div>

                    <!-- Hidden file input for code mode image upload -->
                    <div v-if="codeTab === 'html'" class="mb-2">
                        <label class="secondary-button cursor-pointer" for="static-image">
                            <span class="icon-add"></span>
                            @lang('admin::app.settings.themes.edit.add-image-btn')
                        </label>
                        <input
                            type="file"
                            name="static_image"
                            id="static_image"
                            class="hidden"
                            accept="image/*"
                            ref="static_image"
                            @change="storeImageCodeMode($event)"
                        />
                    </div>

                    <textarea
                        v-show="codeTab === 'html'"
                        v-model="htmlCode"
                        rows="20"
                        class="w-full rounded border border-gray-300 p-3 font-mono text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    ></textarea>

                    <textarea
                        v-show="codeTab === 'css'"
                        v-model="cssCode"
                        rows="20"
                        class="w-full rounded border border-gray-300 p-3 font-mono text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    ></textarea>

                    <div
                        v-show="codeTab === 'preview'"
                        class="rounded bg-gray-50 p-4 dark:bg-gray-800"
                        v-html="getCodePreview()"
                    ></div>
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
                    mode: 'visual',
                    codeTab: 'html',
                    dragIndex: null,
                    dragOverIndex: null,

                    images: @json($theme->translate($currentLocale->code)['options']['images'] ?? []),
                    textContent: @json($theme->translate($currentLocale->code)['options']['text'] ?? ''),
                    layout: @json($theme->translate($currentLocale->code)['options']['layout'] ?? 'grid'),
                    htmlCode: `{!! $theme->translate($currentLocale->code)['options']['html'] ?? '' !!}`,
                    cssCode: `{!! $theme->translate($currentLocale->code)['options']['css'] ?? '' !!}`,

                    layoutOptions: [
                        { value: 'grid', label: '@lang("admin::app.settings.themes.edit.layout-grid")' },
                        { value: 'slider', label: '@lang("admin::app.settings.themes.edit.layout-slider")' },
                        { value: 'masonry', label: '@lang("admin::app.settings.themes.edit.layout-masonry")' },
                        { value: 'banner', label: '@lang("admin::app.settings.themes.edit.layout-banner")' },
                    ],
                };
            },

            methods: {
                addImagesFromFile(event) {
                    const files = Array.from(event.target.files);
                    files.forEach(file => {
                        if (!file.type.startsWith('image/')) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: '@lang("admin::app.settings.themes.edit.image-upload-message")'
                            });
                            return;
                        }
                        this.uploadImage(file, (url) => {
                            this.images.push({
                                url: url,
                                alt: '',
                                link: '',
                                caption: '',
                                position: this.images.length,
                            });
                        });
                    });
                    event.target.value = '';
                },

                replaceImage(index, event) {
                    const file = event.target.files[0];
                    if (!file || !file.type.startsWith('image/')) return;

                    this.uploadImage(file, (url) => {
                        this.images[index].url = url;
                    });
                    event.target.value = '';
                },

                removeImage(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        message: '@lang("admin::app.settings.themes.edit.confirm-delete-image")',
                        agree: () => {
                            this.images.splice(index, 1);
                        },
                    });
                },

                uploadImage(file, callback) {
                    let formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', file);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            callback(response.data);
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: '@lang("admin::app.settings.themes.edit.image-uploaded")'
                            });
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || '@lang("admin::app.settings.themes.edit.image-upload-failed")'
                            });
                        });
                },

                storeImageCodeMode($event) {
                    let selectedImage = $event.target.files[0];
                    if (!selectedImage) return;

                    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
                    if (!allowedTypes.includes(selectedImage.type)) return;

                    let formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', selectedImage);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            this.htmlCode += `\n<img class="lazy" src="" data-src="${response.data}">`;
                        })
                        .catch((error) => {
                            if (error.response?.status == 422) {
                                this.$emitter.emit('add-flash', { type: 'warning', message: error.response.data.message });
                            }
                        });
                },

                // Drag and drop reordering
                onDragStart(index, event) {
                    this.dragIndex = index;
                    event.dataTransfer.effectAllowed = 'move';
                },

                onDragOver(index, event) {
                    this.dragOverIndex = index;
                },

                onDrop(toIndex) {
                    if (this.dragIndex === null || this.dragIndex === toIndex) return;
                    const item = this.images.splice(this.dragIndex, 1)[0];
                    this.images.splice(toIndex, 0, item);
                    this.dragIndex = null;
                    this.dragOverIndex = null;
                },

                onDragEnd() {
                    this.dragIndex = null;
                    this.dragOverIndex = null;
                },

                // Preview rendering
                getPreview() {
                    if (this.layout === 'grid') return this.getGridPreview();
                    if (this.layout === 'slider') return this.getSliderPreview();
                    if (this.layout === 'masonry') return this.getMasonryPreview();
                    if (this.layout === 'banner') return this.getBannerPreview();
                    return '';
                },

                getGridPreview() {
                    let html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                    this.images.forEach((image, i) => {
                        if (!image.url) return;
                        const img = `<img src="${image.url}" alt="${image.alt || ''}" class="w-full h-auto rounded-lg" loading="lazy" />`;
                        const caption = image.caption ? `<p class="mt-1 text-xs text-gray-500 text-center">${image.caption}</p>` : '';
                        const content = `<div class="text-center">${img}${caption}</div>`;
                        html += image.link
                            ? `<a href="${image.link}" class="block">${content}</a>`
                            : content;
                    });
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700 whitespace-pre-line">${this.textContent}</div>`;
                    }
                    return html;
                },

                getSliderPreview() {
                    let html = '<div class="relative overflow-hidden rounded-lg">';
                    if (this.images.length > 0 && this.images[0].url) {
                        const image = this.images[0];
                        const img = `<img src="${image.url}" alt="${image.alt || ''}" class="w-full h-64 object-cover" />`;
                        html += image.link ? `<a href="${image.link}">${img}</a>` : img;
                        if (image.caption) {
                            html += `<div class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-center text-white text-sm">${image.caption}</div>`;
                        }
                    }
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700 whitespace-pre-line">${this.textContent}</div>`;
                    }
                    return html;
                },

                getMasonryPreview() {
                    let html = '<div class="columns-2 md:columns-3 lg:columns-4 gap-4">';
                    this.images.forEach((image, i) => {
                        if (!image.url) return;
                        const img = `<img src="${image.url}" alt="${image.alt || ''}" class="w-full mb-4 rounded-lg" loading="lazy" />`;
                        const caption = image.caption ? `<p class="text-xs text-gray-500 text-center mb-2">${image.caption}</p>` : '';
                        const content = `<div class="break-inside-avoid">${img}${caption}</div>`;
                        html += image.link
                            ? `<a href="${image.link}" class="block">${content}</a>`
                            : content;
                    });
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700 whitespace-pre-line">${this.textContent}</div>`;
                    }
                    return html;
                },

                getBannerPreview() {
                    let html = '<div class="relative">';
                    if (this.images.length > 0 && this.images[0].url) {
                        const image = this.images[0];
                        html += `<img src="${image.url}" alt="${image.alt || ''}" class="w-full h-64 object-cover rounded-lg" />`;
                        if (this.textContent || image.caption) {
                            html += `<div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-lg">
                                <div class="text-white text-center p-4">
                                    ${image.caption ? `<h3 class="text-lg font-bold mb-2">${image.caption}</h3>` : ''}
                                    <p>${this.textContent || ''}</p>
                                </div>
                            </div>`;
                        }
                    }
                    html += '</div>';
                    return html;
                },

                getCodePreview() {
                    let html = this.htmlCode || '';
                    html = html.replaceAll('src=""', '').replaceAll('data-src', 'src');
                    return html + '<style>' + (this.cssCode || '') + '</style>';
                },
            },
        });
    </script>
@endPushOnce

@pushOnce('styles')
    <style>
        .icon-sort-down::before { content: "\2195"; }
    </style>
@endPushOnce
