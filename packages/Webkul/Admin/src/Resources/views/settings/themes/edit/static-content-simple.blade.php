<v-static-content-simple :errors="errors">
    <x-admin::shimmer.settings.themes.static-content />
</v-static-content-simple>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-static-content-simple-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.static-content')
                        </p>
                        
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            Upload images in specific positions - no HTML required
                        </p>
                    </div>

                    <!-- Add Image Button -->
                    <div
                        class="secondary-button"
                        @click="addImage"
                    >
                        + Add Image
                    </div>
                </div>

                <!-- Section Title -->
                <div class="mt-4">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            Section Title (Optional)
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="{{ $currentLocale->code }}[section_title]"
                            v-model="sectionTitle"
                            placeholder="e.g., Featured Collections"
                        />
                    </x-admin::form.control-group>
                </div>

                <!-- Layout Selection -->
                <div class="mt-4">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            Layout Style
                        </x-admin::form.control-group.label>

                        <select
                            name="{{ $currentLocale->code }}[layout]"
                            v-model="layout"
                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                        >
                            <option value="grid-2">2 Column Grid</option>
                            <option value="grid-3">3 Column Grid</option>
                            <option value="grid-4">4 Column Grid</option>
                            <option value="banner">Full Width Banner</option>
                        </select>
                    </x-admin::form.control-group>
                </div>

                <!-- Hidden inputs for deleted images -->
                <template v-for="(deletedImage, index) in deletedImages">
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[deleted_images]['+ index +'][image]'"
                        :value="deletedImage.image"
                    />
                </template>

                <!-- Image List -->
                <div class="grid pt-4" v-if="images.length">
                    <div
                        v-for="(image, index) in images"
                        :key="'image-' + index"
                        class="flex cursor-pointer justify-between gap-2.5 py-5"
                        :class="{
                            'border-b border-slate-300 dark:border-gray-800': index < images.length - 1
                        }"
                    >
                        <!-- Hidden File Input -->
                        <input
                            type="file"
                            class="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                            :ref="'imageInput_' + index"
                            accept="image/*"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][title]'"
                            :value="image.title"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][link]'"
                            :value="image.link"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                            :value="image.image"
                        />

                        <input
                            type="hidden"
                            :name="'{{ $currentLocale->code }}[options]['+ index +'][position]'"
                            :value="index + 1"
                        />

                        <!-- Image Details -->
                        <div class="flex gap-2.5">
                            <!-- Position Badge -->
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white font-bold">
                                @{{ index + 1 }}
                            </div>

                            <div class="grid place-content-start gap-1.5">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Title:</span>
                                    <span class="text-gray-600 transition-all dark:text-gray-300">
                                        @{{ image.title || 'No title' }}
                                    </span>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300" v-if="image.link">
                                    <span class="font-semibold">Link:</span>
                                    <span class="text-gray-600 transition-all dark:text-gray-300">
                                        @{{ image.link }}
                                    </span>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    <span class="font-semibold">Image:</span>
                                    <span class="text-gray-600 transition-all dark:text-gray-300">
                                        <a
                                            v-if="image.image"
                                            :href="'{{ config('app.url') }}/' + image.image"
                                            :ref="'image_' + index"
                                            target="_blank"
                                            class="text-blue-600 transition-all hover:underline ltr:ml-2 rtl:mr-2"
                                        >
                                            <span :ref="'imageName_' + index">
                                                @{{ image.image.split('/').pop() }}
                                            </span>
                                        </a>
                                        <span v-else class="text-red-600">No image uploaded</span>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <div class="flex items-center gap-x-5">
                                <p
                                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                                    @click="editImage(image, index)"
                                >
                                    @lang('admin::app.settings.themes.edit.edit')
                                </p>

                                <p
                                    class="cursor-pointer text-red-600 transition-all hover:underline"
                                    @click="removeImage(index)"
                                >
                                    @lang('admin::app.settings.themes.edit.delete')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="No images"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            No images added yet
                        </p>
                        
                        <p class="text-gray-400">
                            Click "Add Image" to upload images in specific positions
                        </p>
                    </div>
                </div>
            </div>

            <!-- Add/Edit Image Modal -->
            <x-admin::form v-slot="{ errors, handleSubmit }" as="div">
                <form
                    @submit.prevent="handleSubmit($event, saveImage)"
                    enctype="multipart/form-data"
                    ref="imageForm"
                >
                    <x-admin::modal ref="imageModal">
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <template v-if="!isUpdating">
                                    Add Image to Position @{{ images.length + 1 }}
                                </template>
                                <template v-else>
                                    Edit Image at Position @{{ selectedImageIndex + 1 }}
                                </template>
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Image Title
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[title]"
                                    rules="required"
                                    v-model="selectedImage.title"
                                    placeholder="e.g., Summer Collection"
                                    label="Image Title"
                                />

                                <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[title]" />
                            </x-admin::form.control-group>

                            <!-- Link -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    Link URL (Optional)
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[link]"
                                    v-model="selectedImage.link"
                                    placeholder="e.g., /collections/summer"
                                />
                            </x-admin::form.control-group>

                            <!-- Image Upload -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    Image File
                                </x-admin::form.control-group.label>

                                <div class="hidden">
                                    <x-admin::media.images
                                        ::key="'image_hidden_' + mediaComponentKey"
                                        name="image_file"
                                        ::uploaded-images='selectedImageMediaImages'
                                    />
                                </div>

                                <v-media-images
                                    :key="'image_file_' + mediaComponentKey"
                                    name="image_file"
                                    :uploaded-images='selectedImageMediaImages'
                                >
                                </v-media-images>

                                <x-admin::form.control-group.error control-name="image_file" />
                            </x-admin::form.control-group>

                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                Recommended: 1920x1080px. Images will be auto-optimized to WebP format.
                            </p>
                        </x-slot>

                        <x-slot:footer>
                            <button
                                type="button"
                                class="primary-button justify-center"
                                @click="handleSubmit($event, saveImage)"
                            >
                                @lang('admin::app.settings.themes.edit.save-btn')
                            </button>
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-static-content-simple', {
            template: '#v-static-content-simple-template',

            props: ['errors'],

            data() {
                return {
                    images: [],
                    deletedImages: [],
                    selectedImage: {},
                    selectedImageMediaImages: [],
                    selectedImageOriginalImage: null,
                    mediaComponentKey: 0,
                    selectedImageIndex: null,
                    isUpdating: false,
                    sectionTitle: '',
                    layout: 'grid-2',
                };
            },

            created() {
                const options = @json($theme->translate($currentLocale->code)['options'] ?? null);
                
                if (options && options.images) {
                    this.images = options.images;
                }

                this.sectionTitle = options?.section_title || '';
                this.layout = options?.layout || 'grid-2';
            },

            methods: {
                addImage() {
                    this.openImageModal();
                },

                editImage(image, index) {
                    this.openImageModal(image, index);
                },

                openImageModal(image = null, index = null) {
                    this.resetSelectedImage();

                    if (image) {
                        this.isUpdating = true;
                        this.selectedImageIndex = index;
                        this.selectedImage = { ...image };
                        this.selectedImageOriginalImage = image.image;
                        this.selectedImageMediaImages = image.image
                            ? [{ id: `image_${index}`, url: '{{ asset('/') }}' + image.image }]
                            : [];
                    }

                    this.mediaComponentKey++;
                    this.$refs.imageModal.toggle();
                },

                saveImage(_, { resetForm, setErrors }) {
                    const formData = new FormData(this.$refs.imageForm);
                    const imageFile = formData.get("image_file[]");
                    const hasUploadedImage = imageFile instanceof File && imageFile.name !== '';

                    try {
                        const imageData = {
                            title: formData.get("{{ $currentLocale->code }}[title]"),
                            link: formData.get("{{ $currentLocale->code }}[link]"),
                        };

                        if (!this.hasImageFile(formData, hasUploadedImage)) {
                            throw new Error("Please upload an image file");
                        }

                        const imageIndex = this.upsertImage(imageData);

                        if (hasUploadedImage) {
                            this.setFile(imageFile, imageIndex);
                            this.markImageForDeletion();
                        }

                        resetForm();
                        this.resetSelectedImage();
                        this.$refs.imageModal.toggle();

                    } catch (error) {
                        setErrors({
                            image_file: [error.message],
                        });
                    }
                },

                upsertImage(imageData) {
                    if (this.isUpdating) {
                        this.images[this.selectedImageIndex] = {
                            ...this.images[this.selectedImageIndex],
                            ...imageData,
                        };
                        return this.selectedImageIndex;
                    }

                    this.images.push(imageData);
                    return this.images.length - 1;
                },

                markImageForDeletion() {
                    if (!this.isUpdating || !this.selectedImageOriginalImage) {
                        return;
                    }

                    this.deletedImages.push({
                        image: this.selectedImageOriginalImage,
                    });
                },

                hasImageFile(formData, hasUploadedImage) {
                    if (hasUploadedImage) {
                        return true;
                    }

                    return Array.from(formData.keys()).some((key) => {
                        return key === 'image_file[]' || key.startsWith('image_file[');
                    });
                },

                setFile(file, index) {
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);

                    setTimeout(() => {
                        if (this.$refs['image_' + index] && this.$refs['image_' + index][0]) {
                            this.$refs['image_' + index][0].href = URL.createObjectURL(file);
                            this.$refs['imageName_' + index][0].innerHTML = file.name;
                        }
                        
                        if (this.$refs['imageInput_' + index] && this.$refs['imageInput_' + index][0]) {
                            this.$refs['imageInput_' + index][0].files = dataTransfer.files;
                        }
                    }, 0);
                },

                removeImage(index) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            const image = this.images[index];

                            if (!image) {
                                return;
                            }

                            if (image.image) {
                                this.deletedImages.push(image);
                            }

                            this.images.splice(index, 1);
                        },
                    });
                },

                resetSelectedImage() {
                    this.selectedImage = {};
                    this.selectedImageMediaImages = [];
                    this.selectedImageOriginalImage = null;
                    this.selectedImageIndex = null;
                    this.isUpdating = false;
                },
            },
        });
    </script>
@endPushOnce
