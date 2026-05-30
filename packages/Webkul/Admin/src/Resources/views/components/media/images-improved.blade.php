@php
    use Webkul\MagicAI\AiProvider;

    $enabledProviders = array_filter(explode(',', core()->getConfigData('magic_ai.admin_features.image_generation.providers') ?? ''));
    
    $models = AiProvider::modelsForProviders($enabledProviders, 'image');
    
    $defaultModel = $models[0]['value'] ?? '';
@endphp

@props([
    'name'             => 'images',
    'allowMultiple'    => false,
    'showPlaceholders' => false,
    'uploadedImages'   => [],
    'width'            => '120px',
    'height'           => '120px'
])

<v-media-images-improved
    name="{{ $name }}"
    v-bind:allow-multiple="{{ $allowMultiple ? 'true' : 'false' }}"
    v-bind:show-placeholders="{{ $showPlaceholders ? 'true' : 'false' }}"
    :uploaded-images='{{ json_encode($uploadedImages) }}'
    width="{{ $width }}"
    height="{{ $height }}"
    :errors="errors"
>
    <x-admin::shimmer.image class="h-[110px] w-[110px] rounded" />
</v-media-images-improved>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-media-images-improved-template"
    >
        <!-- Panel Content -->
        <div class="grid">
            <div class="flex flex-wrap gap-4">
                <!-- Upload Image Button -->
                <template v-if="allowMultiple || images.length == 0">
                    <!-- Upload Image Button with Better Styling -->
                    <label
                        class="group grid h-[160px] w-[160px] cursor-pointer items-center justify-items-center rounded-lg border-2 border-dashed border-gray-300 transition-all hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:hover:border-blue-500 dark:hover:bg-gray-800"
                        :for="$.uid + '_imageInput'"
                    >
                        <div class="flex flex-col items-center gap-2">
                            <span class="icon-image text-4xl text-gray-400 group-hover:text-blue-500"></span>

                            <p class="text-center text-sm font-semibold text-gray-600 group-hover:text-blue-600 dark:text-gray-300">
                                @lang('admin::app.components.media.images.add-image-btn')
                            </p>
                            
                            <span class="text-xs text-gray-400">
                                @lang('admin::app.components.media.images.allowed-types')
                            </span>
                        </div>

                        <input
                            type="file"
                            class="hidden"
                            :id="$.uid + '_imageInput'"
                            accept="image/*"
                            :multiple="allowMultiple"
                            :ref="$.uid + '_imageInput'"
                            @change="add"
                        />
                    </label>
                </template>

                <!-- Uploaded Images with Enhanced Preview -->
                <draggable
                    class="flex flex-wrap gap-4"
                    ghost-class="draggable-ghost"
                    v-bind="{animation: 200}"
                    :list="images"
                    item-key="id"
                >
                    <template #item="{ element, index }">
                        <v-media-image-item-improved
                            :name="name"
                            :index="index"
                            :image="element"
                            :width="width"
                            :height="height"
                            @onRemove="remove($event)"
                        >
                        </v-media-image-item-improved>
                    </template>
                </draggable>
            </div>
        </div>  
    </script>

    <script type="text/x-template" id="v-media-image-item-improved-template">
        <div class="group relative overflow-hidden rounded-lg border-2 border-gray-200 transition-all hover:border-blue-500 hover:shadow-lg dark:border-gray-700">
            <!-- Image Preview Container -->
            <div 
                class="relative flex items-center justify-center bg-gray-50 dark:bg-gray-800"
                :style="{'width': '160px', 'height': '160px'}"
            >
                <!-- Image Preview -->
                <img
                    :src="image.url"
                    class="max-h-full max-w-full object-contain"
                    :alt="image.name || 'Image'"
                />

                <!-- Loading Overlay -->
                <div 
                    v-if="isLoading"
                    class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50"
                >
                    <div class="h-8 w-8 animate-spin rounded-full border-4 border-white border-t-transparent"></div>
                </div>
            </div>

            <!-- Action Overlay - Always Visible on Mobile, Hover on Desktop -->
            <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between bg-gradient-to-t from-black/80 to-transparent p-3 opacity-100 transition-opacity md:opacity-0 md:group-hover:opacity-100">
                <!-- Image Name (if available) -->
                <p class="truncate text-xs font-medium text-white" v-if="image.name">
                    @{{ image.name }}
                </p>
                <span v-else></span>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <!-- Preview Button -->
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/30"
                        @click="preview"
                        title="Preview"
                    >
                        <span class="icon-eye text-lg"></span>
                    </button>

                    <!-- Change Button -->
                    <label
                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition-all hover:bg-white/30"
                        :for="$.uid + '_imageInput_' + index"
                        title="Change Image"
                    >
                        <span class="icon-edit text-lg"></span>
                    </label>

                    <!-- Delete Button -->
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-red-500/80 text-white backdrop-blur-sm transition-all hover:bg-red-600"
                        @click="confirmRemove"
                        title="Delete"
                    >
                        <span class="icon-delete text-lg"></span>
                    </button>
                </div>
            </div>

            <!-- Hidden Inputs -->
            <input
                type="hidden"
                :name="name + '[' + image.id + ']'"
                v-if="! image.is_new"
            />

            <input
                type="file"
                :name="name + '[]'"
                class="hidden"
                accept="image/*"
                :id="$.uid + '_imageInput_' + index"
                :ref="$.uid + '_imageInput_' + index"
                @change="edit"
            />
        </div>

        <!-- Preview Modal -->
        <Teleport to="body">
            <div
                v-if="showPreview"
                class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/90 p-4"
                @click="showPreview = false"
            >
                <div class="relative max-h-[90vh] max-w-[90vw]">
                    <img
                        :src="image.url"
                        class="max-h-[90vh] max-w-[90vw] object-contain"
                        @click.stop
                    />
                    <button
                        class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm hover:bg-white/30"
                        @click="showPreview = false"
                    >
                        <span class="icon-cross text-2xl"></span>
                    </button>
                </div>
            </div>
        </Teleport>
    </script>

    <script type="module">
        app.component('v-media-images-improved', {
            template: '#v-media-images-improved-template',

            props: {
                name: {
                    type: String, 
                    default: 'images',
                },

                allowMultiple: {
                    type: Boolean,
                    default: false,
                },

                showPlaceholders: {
                    type: Boolean,
                    default: false,
                },

                uploadedImages: {
                    type: Array,
                    default: () => []
                },

                width: {
                    type: String,
                    default: '120px'
                },

                height: {
                    type: String,
                    default: '120px'
                },

                errors: {
                    type: Object,
                    default: () => {}
                }
            },

            data() {
                return {
                    images: [],
                }
            },

            mounted() {
                this.images = this.uploadedImages;
            },

            methods: {
                add() {
                    let imageInput = this.$refs[this.$.uid + '_imageInput'];

                    if (imageInput.files == undefined) {
                        return;
                    }

                    const validFiles = Array.from(imageInput.files).every(file => file.type.includes('image/'));

                    if (! validFiles) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('admin::app.components.media.images.not-allowed-error')"
                        });

                        return;
                    }

                    Array.from(imageInput.files).forEach((file, index) => {
                        this.images.push({
                            id: 'image_' + this.images.length,
                            url: URL.createObjectURL(file),
                            file: file,
                            name: file.name,
                            is_new: 1
                        });
                    });

                    // Reset input
                    imageInput.value = '';
                },

                remove(image) {
                    let index = this.images.indexOf(image);
                    this.images.splice(index, 1);
                },
            },
        });

        app.component('v-media-image-item-improved', {
            template: '#v-media-image-item-improved-template',

            props: ['index', 'image', 'name', 'width', 'height'],

            data() {
                return {
                    showPreview: false,
                    isLoading: false,
                }
            },

            mounted() {
                if (this.image.file instanceof File) {
                    this.setFile(this.image.file);
                }
            },

            methods: {
                edit() {
                    let imageInput = this.$refs[this.$.uid + '_imageInput_' + this.index];

                    if (imageInput.files == undefined) {
                        return;
                    }

                    const validFiles = Array.from(imageInput.files).every(file => file.type.includes('image/'));

                    if (! validFiles) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('admin::app.components.media.images.not-allowed-error')"
                        });

                        return;
                    }

                    const file = imageInput.files[0];
                    
                    this.isLoading = true;
                    
                    this.setFile(file);
                    this.readFile(file);
                    
                    setTimeout(() => {
                        this.isLoading = false;
                    }, 500);
                },

                confirmRemove() {
                    this.$emitter.emit('open-confirm-modal', {
                        message: "@lang('admin::app.components.media.images.delete-confirmation')",
                        agree: () => {
                            this.remove();
                        },
                    });
                },

                remove() {
                    this.$emit('onRemove', this.image)
                },

                preview() {
                    this.showPreview = true;
                },

                setFile(file) {
                    this.image.is_new = 1;
                    this.image.name = file.name;

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);

                    this.$refs[this.$.uid + '_imageInput_' + this.index].files = dataTransfer.files;
                },

                readFile(file) {
                    let reader = new FileReader();

                    reader.onload = (e) => {
                        this.image.url = e.target.result;
                    }

                    reader.readAsDataURL(file);
                },
            }
        });
    </script>
@endPushOnce
