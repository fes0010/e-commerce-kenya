<v-static-content-improved :errors="errors">
    <x-admin::shimmer.settings.themes.static-content />
</v-static-content-improved>

@pushOnce('scripts')
    <script type="text/x-template" id="v-static-content-improved-template">
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.static-content')
                        </p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            Add images and content without writing HTML
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
                            Visual Editor
                        </button>
                        <button
                            type="button"
                            class="secondary-button"
                            :class="{'primary-button': mode === 'code'}"
                            @click="mode = 'code'"
                        >
                            <span class="icon-code"></span>
                            Code Editor
                        </button>
                    </div>
                </div>

                <!-- Visual Editor Mode -->
                <div v-show="mode === 'visual'" class="space-y-4">
                    <!-- Image Gallery Section -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                                Image Gallery
                            </h3>
                            <button
                                type="button"
                                class="secondary-button"
                                @click="addImage"
                            >
                                <span class="icon-add"></span>
                                Add Image
                            </button>
                        </div>

                        <!-- Images Grid -->
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                            <div
                                v-for="(image, index) in images"
                                :key="index"
                                class="group relative overflow-hidden rounded-lg border-2 border-gray-200 transition-all hover:border-blue-500 dark:border-gray-700"
                            >
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
                                <div class="p-2">
                                    <input
                                        type="text"
                                        v-model="image.alt"
                                        placeholder="Image description"
                                        class="w-full rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                    />
                                    <input
                                        type="text"
                                        v-model="image.link"
                                        placeholder="Link URL (optional)"
                                        class="mt-1 w-full rounded border border-gray-300 px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-800"
                                    />
                                </div>

                                <!-- Actions Overlay -->
                                <div class="absolute right-2 top-2 flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                    <label
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white/90 text-gray-700 shadow-lg hover:bg-white"
                                        :for="'image-upload-' + index"
                                    >
                                        <span class="icon-edit text-sm"></span>
                                    </label>
                                    <input
                                        type="file"
                                        :id="'image-upload-' + index"
                                        class="hidden"
                                        accept="image/*"
                                        @change="updateImage(index, $event)"
                                    />
                                    <button
                                        type="button"
                                        class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500/90 text-white shadow-lg hover:bg-red-600"
                                        @click="removeImage(index)"
                                    >
                                        <span class="icon-delete text-sm"></span>
                                    </button>
                                </div>

                                <!-- Hidden input for form submission -->
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
                            </div>

                            <!-- Add Image Card -->
                            <label
                                class="flex aspect-square cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 transition-all hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                for="new-image-upload"
                            >
                                <span class="icon-add text-4xl text-gray-400"></span>
                                <span class="mt-2 text-sm text-gray-500">Add Image</span>
                            </label>
                            <input
                                type="file"
                                id="new-image-upload"
                                class="hidden"
                                accept="image/*"
                                @change="addImageFromFile($event)"
                            />
                        </div>
                    </div>

                    <!-- Text Content Section -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            Text Content
                        </h3>
                        <textarea
                            v-model="textContent"
                            rows="6"
                            class="w-full rounded border border-gray-300 p-3 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            placeholder="Add your text content here..."
                        ></textarea>
                        <input
                            type="hidden"
                            name="{{ $currentLocale->code }}[options][text]"
                            :value="textContent"
                        />
                    </div>

                    <!-- Layout Options -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            Layout Options
                        </h3>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="layout"
                                    value="grid"
                                    name="{{ $currentLocale->code }}[options][layout]"
                                />
                                <span class="text-sm">Grid</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="layout"
                                    value="slider"
                                    name="{{ $currentLocale->code }}[options][layout]"
                                />
                                <span class="text-sm">Slider</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="layout"
                                    value="masonry"
                                    name="{{ $currentLocale->code }}[options][layout]"
                                />
                                <span class="text-sm">Masonry</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input
                                    type="radio"
                                    v-model="layout"
                                    value="banner"
                                    name="{{ $currentLocale->code }}[options][layout]"
                                />
                                <span class="text-sm">Banner</span>
                            </label>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                            Preview
                        </h3>
                        <div class="rounded bg-gray-50 p-4 dark:bg-gray-800" v-html="getPreview()"></div>
                    </div>
                </div>

                <!-- Code Editor Mode (Original) -->
                <div v-show="mode === 'code'">
                    <div class="mb-4 flex gap-4 border-b-2">
                        <button
                            type="button"
                            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                            :class="{'-mb-px border-b-2 border-blue-600': codeTab === 'html'}"
                            @click="codeTab = 'html'"
                        >
                            HTML
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                            :class="{'-mb-px border-b-2 border-blue-600': codeTab === 'css'}"
                            @click="codeTab = 'css'"
                        >
                            CSS
                        </button>
                    </div>

                    <textarea
                        v-show="codeTab === 'html'"
                        v-model="htmlCode"
                        rows="20"
                        class="w-full rounded border border-gray-300 p-3 font-mono text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        name="{{ $currentLocale->code }}[options][html]"
                    ></textarea>

                    <textarea
                        v-show="codeTab === 'css'"
                        v-model="cssCode"
                        rows="20"
                        class="w-full rounded border border-gray-300 p-3 font-mono text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        name="{{ $currentLocale->code }}[options][css]"
                    ></textarea>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-static-content-improved', {
            template: '#v-static-content-improved-template',

            props: ['errors'],

            data() {
                return {
                    mode: 'visual', // 'visual' or 'code'
                    codeTab: 'html',
                    images: @json($theme->translate($currentLocale->code)['options']['images'] ?? []),
                    textContent: @json($theme->translate($currentLocale->code)['options']['text'] ?? ''),
                    layout: @json($theme->translate($currentLocale->code)['options']['layout'] ?? 'grid'),
                    htmlCode: `{!! $theme->translate($currentLocale->code)['options']['html'] ?? '' !!}`,
                    cssCode: `{!! $theme->translate($currentLocale->code)['options']['css'] ?? '' !!}`,
                };
            },

            methods: {
                addImage() {
                    this.images.push({
                        url: '',
                        alt: '',
                        link: ''
                    });
                },

                addImageFromFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: 'Please select a valid image file'
                        });
                        return;
                    }

                    this.uploadImage(file, (url) => {
                        this.images.push({
                            url: url,
                            alt: '',
                            link: ''
                        });
                    });

                    event.target.value = '';
                },

                updateImage(index, event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: 'Please select a valid image file'
                        });
                        return;
                    }

                    this.uploadImage(file, (url) => {
                        this.images[index].url = url;
                    });

                    event.target.value = '';
                },

                removeImage(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        message: 'Are you sure you want to delete this image?',
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
                                message: 'Image uploaded successfully'
                            });
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Failed to upload image'
                            });
                        });
                },

                getPreview() {
                    if (this.layout === 'grid') {
                        return this.getGridPreview();
                    } else if (this.layout === 'slider') {
                        return this.getSliderPreview();
                    } else if (this.layout === 'masonry') {
                        return this.getMasonryPreview();
                    } else if (this.layout === 'banner') {
                        return this.getBannerPreview();
                    }
                    return '';
                },

                getGridPreview() {
                    let html = '<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">';
                    this.images.forEach(image => {
                        if (image.url) {
                            const imgTag = `<img src="${image.url}" alt="${image.alt}" class="w-full h-auto rounded-lg" />`;
                            html += image.link 
                                ? `<a href="${image.link}" class="block">${imgTag}</a>`
                                : `<div>${imgTag}</div>`;
                        }
                    });
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700">${this.textContent}</div>`;
                    }
                    return html;
                },

                getSliderPreview() {
                    let html = '<div class="relative overflow-hidden rounded-lg">';
                    if (this.images.length > 0 && this.images[0].url) {
                        const image = this.images[0];
                        const imgTag = `<img src="${image.url}" alt="${image.alt}" class="w-full h-auto" />`;
                        html += image.link 
                            ? `<a href="${image.link}">${imgTag}</a>`
                            : imgTag;
                    }
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700">${this.textContent}</div>`;
                    }
                    return html;
                },

                getMasonryPreview() {
                    let html = '<div class="columns-2 md:columns-3 lg:columns-4 gap-4">';
                    this.images.forEach(image => {
                        if (image.url) {
                            const imgTag = `<img src="${image.url}" alt="${image.alt}" class="w-full mb-4 rounded-lg" />`;
                            html += image.link 
                                ? `<a href="${image.link}" class="block">${imgTag}</a>`
                                : `<div>${imgTag}</div>`;
                        }
                    });
                    html += '</div>';
                    if (this.textContent) {
                        html += `<div class="mt-4 text-gray-700">${this.textContent}</div>`;
                    }
                    return html;
                },

                getBannerPreview() {
                    let html = '<div class="relative">';
                    if (this.images.length > 0 && this.images[0].url) {
                        const image = this.images[0];
                        html += `<img src="${image.url}" alt="${image.alt}" class="w-full h-64 object-cover rounded-lg" />`;
                        if (this.textContent) {
                            html += `<div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 rounded-lg">
                                <div class="text-white text-center p-4">${this.textContent}</div>
                            </div>`;
                        }
                    }
                    html += '</div>';
                    return html;
                },
            },
        });
    </script>
@endPushOnce
