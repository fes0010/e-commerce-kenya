<v-static-content-visual :errors="errors">
    <x-admin::shimmer.settings.themes.static-content />
</v-static-content-visual>

<!-- Visual Static Content Editor Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-static-content-visual-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.static-content')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            Click on any image slot to upload or change images
                        </p>
                    </div>

                    <div class="flex gap-2.5">
                        <!-- Toggle between Visual and Code mode -->
                        <button
                            type="button"
                            class="secondary-button"
                            @click="toggleMode"
                        >
                            <span v-if="isVisualMode">Switch to Code Editor</span>
                            <span v-else>Switch to Visual Editor</span>
                        </button>
                    </div>
                </div>

                <!-- Visual Mode -->
                <div v-if="isVisualMode">
                    <!-- Section Title -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Section Title
                        </label>
                        <input
                            type="text"
                            v-model="content.title"
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            placeholder="e.g., Top Collections"
                        />
                    </div>

                    <!-- Section Description (Optional) -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description (Optional)
                        </label>
                        <textarea
                            v-model="content.description"
                            rows="2"
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            placeholder="Brief description of this section"
                        ></textarea>
                    </div>

                    <!-- Layout Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Layout Style
                        </label>
                        <select
                            v-model="content.layout"
                            class="w-full rounded-md border px-3 py-2 text-sm"
                            @change="updateLayout"
                        >
                            <option value="grid-2">2 Column Grid</option>
                            <option value="grid-3">3 Column Grid</option>
                            <option value="grid-4">4 Column Grid</option>
                            <option value="slider">Image Slider</option>
                            <option value="masonry">Masonry Layout</option>
                            <option value="banner">Full Width Banner</option>
                        </select>
                    </div>

                    <!-- Image Slots -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Images (@{{ content.images.length }} / @{{ maxImages }})
                            </label>
                            <button
                                type="button"
                                class="secondary-button text-sm"
                                @click="addImageSlot"
                                v-if="content.images.length < maxImages"
                            >
                                + Add Image Slot
                            </button>
                        </div>

                        <!-- Image Grid -->
                        <div 
                            class="grid gap-4"
                            :class="{
                                'grid-cols-2': content.layout === 'grid-2',
                                'grid-cols-3': content.layout === 'grid-3',
                                'grid-cols-4': content.layout === 'grid-4',
                                'grid-cols-1': content.layout === 'banner',
                                'grid-cols-2 md:grid-cols-3': content.layout === 'masonry' || content.layout === 'slider'
                            }"
                        >
                            <div
                                v-for="(image, index) in content.images"
                                :key="'image-' + index"
                                class="relative border-2 border-dashed border-gray-300 rounded-lg overflow-hidden hover:border-blue-500 transition-colors"
                                :class="{'aspect-square': content.layout !== 'banner', 'aspect-video': content.layout === 'banner'}"
                            >
                                <!-- Hidden File Input -->
                                <input
                                    type="file"
                                    :ref="'imageInput_' + index"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleImageUpload($event, index)"
                                />

                                <!-- Image Preview or Placeholder -->
                                <div
                                    class="w-full h-full flex items-center justify-center cursor-pointer bg-gray-50 dark:bg-gray-800"
                                    @click="$refs['imageInput_' + index][0].click()"
                                >
                                    <template v-if="image.preview || image.url">
                                        <img
                                            :src="image.preview || image.url"
                                            class="w-full h-full object-cover"
                                            :alt="image.alt || 'Image ' + (index + 1)"
                                        />
                                    </template>
                                    <template v-else>
                                        <div class="text-center p-4">
                                            <span class="icon-image text-4xl text-gray-400 mb-2"></span>
                                            <p class="text-sm text-gray-500">Click to upload</p>
                                            <p class="text-xs text-gray-400 mt-1">Up to 10MB</p>
                                        </div>
                                    </template>
                                </div>

                                <!-- Image Actions Overlay -->
                                <div
                                    v-if="image.preview || image.url"
                                    class="absolute inset-0 bg-black bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center gap-2"
                                >
                                    <button
                                        type="button"
                                        class="px-3 py-1 bg-white text-sm rounded hover:bg-gray-100"
                                        @click.stop="$refs['imageInput_' + index][0].click()"
                                    >
                                        Change
                                    </button>
                                    <button
                                        type="button"
                                        class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                                        @click.stop="removeImage(index)"
                                    >
                                        Delete
                                    </button>
                                    <button
                                        type="button"
                                        class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                                        @click.stop="previewImage(index)"
                                    >
                                        Preview
                                    </button>
                                </div>

                                <!-- Image Details -->
                                <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900 p-2 border-t">
                                    <input
                                        type="text"
                                        v-model="image.alt"
                                        class="w-full text-xs border rounded px-2 py-1"
                                        placeholder="Image description (optional)"
                                        @click.stop
                                    />
                                    <input
                                        type="text"
                                        v-model="image.link"
                                        class="w-full text-xs border rounded px-2 py-1 mt-1"
                                        placeholder="Link URL (optional)"
                                        @click.stop
                                    />
                                </div>

                                <!-- Position Badge -->
                                <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">
                                    Position @{{ index + 1 }}
                                </div>

                                <!-- Remove Slot Button -->
                                <button
                                    type="button"
                                    class="absolute top-2 right-2 bg-red-600 text-white w-6 h-6 rounded-full hover:bg-red-700"
                                    @click.stop="removeImageSlot(index)"
                                    v-if="content.images.length > 1"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="mt-6 border-t pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Live Preview
                            </h3>
                            <button
                                type="button"
                                class="text-sm text-blue-600 hover:underline"
                                @click="refreshPreview"
                            >
                                Refresh Preview
                            </button>
                        </div>
                        <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-800" v-html="generatePreview()"></div>
                    </div>
                </div>

                <!-- Code Mode (Original HTML/CSS Editor) -->
                <div v-else>
                    <div class="text-center text-sm font-medium text-gray-500 mb-4">
                        <p class="text-yellow-600 mb-2">⚠️ Advanced Mode: Edit HTML/CSS directly</p>
                        <p class="text-xs">Switch to Visual Editor for easier image management</p>
                    </div>
                    
                    <!-- Include original HTML/CSS editor here -->
                    <div class="tabs">
                        <div class="mb-4 flex gap-4 border-b-2 pt-2">
                            <p @click="switchTab('html')">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': activeTab == 'html'}"
                                >
                                    HTML
                                </div>
                            </p>
                            <p @click="switchTab('css')">
                                <div
                                    class="cursor-pointer px-2.5 pb-3.5 text-base font-medium text-gray-600 transition dark:text-gray-300"
                                    :class="{'-mb-px border-b-2 border-blue-600': activeTab == 'css'}"
                                >
                                    CSS
                                </div>
                            </p>
                        </div>
                    </div>

                    <textarea
                        v-if="activeTab === 'html'"
                        v-model="codeMode.html"
                        rows="20"
                        class="w-full font-mono text-sm border rounded p-2"
                    ></textarea>

                    <textarea
                        v-if="activeTab === 'css'"
                        v-model="codeMode.css"
                        rows="20"
                        class="w-full font-mono text-sm border rounded p-2"
                    ></textarea>
                </div>

                <!-- Hidden inputs for form submission -->
                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][html]"
                    :value="generateHTML()"
                />

                <input
                    type="hidden"
                    name="{{ $currentLocale->code }}[options][css]"
                    :value="generateCSS()"
                />

                <!-- Hidden inputs for image uploads -->
                <template v-for="(image, index) in content.images">
                    <input
                        v-if="image.file"
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options][' + index + '][image]'"
                        :value="image.file"
                    />
                </template>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('v-static-content-visual', {
            template: '#v-static-content-visual-template',

            props: ['errors'],

            data() {
                return {
                    isVisualMode: true,
                    activeTab: 'html',
                    
                    content: {
                        title: 'Top Collections',
                        description: '',
                        layout: 'grid-2',
                        images: [
                            { url: '', preview: '', file: null, alt: '', link: '' },
                            { url: '', preview: '', file: null, alt: '', link: '' }
                        ]
                    },

                    codeMode: {
                        html: @json($theme->translate($currentLocale->code)['options']['html'] ?? ''),
                        css: @json($theme->translate($currentLocale->code)['options']['css'] ?? '')
                    },

                    maxImages: 12,
                };
            },

            created() {
                this.parseExistingContent();
            },

            methods: {
                toggleMode() {
                    if (this.isVisualMode) {
                        // Switching to code mode - generate HTML/CSS from visual content
                        this.codeMode.html = this.generateHTML();
                        this.codeMode.css = this.generateCSS();
                    } else {
                        // Switching to visual mode - parse HTML/CSS
                        this.parseExistingContent();
                    }
                    this.isVisualMode = !this.isVisualMode;
                },

                switchTab(tab) {
                    this.activeTab = tab;
                },

                updateLayout() {
                    // Adjust number of images based on layout
                    const layoutDefaults = {
                        'grid-2': 2,
                        'grid-3': 3,
                        'grid-4': 4,
                        'slider': 5,
                        'masonry': 6,
                        'banner': 1
                    };

                    const defaultCount = layoutDefaults[this.content.layout] || 2;
                    
                    while (this.content.images.length < defaultCount) {
                        this.addImageSlot();
                    }
                },

                addImageSlot() {
                    if (this.content.images.length < this.maxImages) {
                        this.content.images.push({
                            url: '',
                            preview: '',
                            file: null,
                            alt: '',
                            link: ''
                        });
                    }
                },

                removeImageSlot(index) {
                    if (this.content.images.length > 1) {
                        this.content.images.splice(index, 1);
                    }
                },

                handleImageUpload(event, index) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: 'Please select a valid image file'
                        });
                        return;
                    }

                    // Validate file size (10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: 'Image size must be less than 10MB'
                        });
                        return;
                    }

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.content.images[index].preview = e.target.result;
                        this.content.images[index].file = file;
                    };
                    reader.readAsDataURL(file);

                    // Upload image
                    this.uploadImage(file, index);
                },

                uploadImage(file, index) {
                    const formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', file);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'static_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            this.content.images[index].url = response.data;
                            
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: 'Image uploaded and optimized successfully!'
                            });
                        })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message || 'Image upload failed'
                            });
                        });
                },

                removeImage(index) {
                    this.content.images[index] = {
                        url: '',
                        preview: '',
                        file: null,
                        alt: this.content.images[index].alt,
                        link: this.content.images[index].link
                    };
                },

                previewImage(index) {
                    const image = this.content.images[index];
                    const url = image.preview || image.url;
                    if (url) {
                        window.open(url, '_blank');
                    }
                },

                refreshPreview() {
                    this.$forceUpdate();
                },

                parseExistingContent() {
                    // Parse existing HTML to extract images
                    // This is a simplified parser - you may need to enhance it
                    const html = this.codeMode.html;
                    
                    // Extract title
                    const titleMatch = html.match(/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i);
                    if (titleMatch) {
                        this.content.title = titleMatch[1].replace(/<[^>]*>/g, '');
                    }

                    // Extract images
                    const imgRegex = /<img[^>]+src="([^"]*)"[^>]*>/gi;
                    const matches = [...html.matchAll(imgRegex)];
                    
                    if (matches.length > 0) {
                        this.content.images = matches.map(match => ({
                            url: match[1].replace('data-src=', '').replace(/"/g, ''),
                            preview: '',
                            file: null,
                            alt: '',
                            link: ''
                        }));
                    }
                },

                generateHTML() {
                    if (!this.isVisualMode) {
                        return this.codeMode.html;
                    }

                    const images = this.content.images
                        .filter(img => img.url || img.preview)
                        .map((img, index) => {
                            const imgSrc = img.url || img.preview;
                            const imgTag = `<img src="" data-src="${imgSrc}" class="lazy" alt="${img.alt || 'Image ' + (index + 1)}" loading="lazy">`;
                            
                            if (img.link) {
                                return `<a href="${img.link}">${imgTag}</a>`;
                            }
                            return imgTag;
                        })
                        .join('\n');

                    return `
<div class="static-content-container ${this.content.layout}">
    <div class="static-content-header">
        <h2>${this.content.title}</h2>
        ${this.content.description ? `<p>${this.content.description}</p>` : ''}
    </div>
    <div class="static-content-grid">
        ${images}
    </div>
</div>`;
                },

                generateCSS() {
                    if (!this.isVisualMode) {
                        return this.codeMode.css;
                    }

                    return `
.static-content-container {
    padding: 2rem 0;
}

.static-content-header {
    text-align: center;
    margin-bottom: 2rem;
}

.static-content-header h2 {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.static-content-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.static-content-grid img {
    width: 100%;
    height: auto;
    object-fit: cover;
    border-radius: 0.5rem;
}

.grid-2 .static-content-grid {
    grid-template-columns: repeat(2, 1fr);
}

.grid-3 .static-content-grid {
    grid-template-columns: repeat(3, 1fr);
}

.grid-4 .static-content-grid {
    grid-template-columns: repeat(4, 1fr);
}

.banner .static-content-grid {
    grid-template-columns: 1fr;
}

@media (max-width: 768px) {
    .static-content-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .grid-4 .static-content-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}`;
                },

                generatePreview() {
                    return this.generateHTML() + '<style>' + this.generateCSS() + '</style>';
                }
            },
        });
    </script>
@endPushOnce
