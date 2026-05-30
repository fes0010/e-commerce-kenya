<v-services-content :errors="errors">
    <x-admin::shimmer.settings.themes.services-content />
</v-services-content>

<!-- Services Content Vue Component -->
@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-services-content-template"
    >
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-x-2.5">
                    <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.settings.themes.edit.services-content.services')
                        </p>
                        
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('admin::app.settings.themes.edit.services-content.service-info')
                        </p>
                    </div>
    
                    <!-- Add Services Button -->
                    <div class="flex gap-2.5">
                        <div
                            class="secondary-button"
                            @click="add"
                        >
                            @lang('admin::app.settings.themes.edit.services-content.add-btn')
                        </div>
                    </div>
                </div>

                <template v-for="(deletedService, index) in deletedServices">
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[deleted_services]['+ index +'][service_details]'"
                        :value="deletedService.service_details"
                    />
                </template>

                <div
                    class="grid pt-4"
                    v-if="servicesContent.services.length"
                    v-for="(service_details, index) in servicesContent.services"
                >
                    <!-- Hidden Inputs -->
                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][title]'"
                        :value="service_details.title"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][description]'"
                        :value="service_details.description"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][service_icon]'"
                        :value="service_details.service_icon"
                    />

                    <input
                        type="hidden"
                        :name="'{{ $currentLocale->code }}[options]['+ index +'][image]'"
                        :value="service_details.image"
                    />
                
                    <!-- Service Details Listing -->
                    <div 
                        class="flex cursor-pointer justify-between gap-2.5 py-5"
                        :class="{
                            'border-b border-slate-300 dark:border-gray-800': index < servicesContent.services.length - 1
                        }"
                    >
                        <div class="flex gap-2.5">
                            <!-- Service Image Preview -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="service_details.image"
                                    :src="service_details.image"
                                    class="h-16 w-16 rounded-lg object-cover"
                                    :alt="service_details.title"
                                />
                                <div
                                    v-else-if="service_details.service_icon"
                                    class="flex h-16 w-16 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <span :class="service_details.service_icon + ' text-2xl text-gray-400'"></span>
                                </div>
                                <div
                                    v-else
                                    class="flex h-16 w-16 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <span class="icon-image text-xl text-gray-300"></span>
                                </div>
                            </div>

                            <div class="grid place-content-start gap-1.5">
                                <p class="text-gray-600 dark:text-gray-300">
                                    <div> 
                                        @lang('admin::app.settings.themes.edit.services-content.title'): 

                                        <span class="text-gray-600 transition-all dark:text-gray-300">
                                            @{{ service_details.title }}
                                        </span>
                                    </div>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    <div> 
                                        @lang('admin::app.settings.themes.edit.services-content.description'): 

                                        <span class="text-gray-600 transition-all dark:text-gray-300">
                                            @{{ service_details.description }}
                                        </span>
                                    </div>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300" v-if="service_details.service_icon">
                                    @lang('admin::app.settings.themes.edit.services-content.service-icon'): 

                                    <span class="text-gray-600 transition-all dark:text-gray-300">
                                        @{{ service_details.service_icon }}
                                    </span>
                                </p>

                                <p class="text-gray-600 dark:text-gray-300" v-if="service_details.image">
                                    @lang('admin::app.settings.themes.edit.services-content.service-image'): 

                                    <span class="text-blue-600 transition-all hover:underline">
                                        @{{ service_details.image.split('/').pop() }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Service Actions -->
                        <div class="grid place-content-start gap-1 text-right">
                            <div class="flex items-center gap-x-5">
                                <p 
                                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                                    @click="edit(service_details)"
                                > 
                                    @lang('admin::app.settings.themes.edit.edit')
                                </p>

                                <p 
                                    class="cursor-pointer text-red-600 transition-all hover:underline"
                                    @click="remove(service_details)"
                                > 
                                    @lang('admin::app.settings.themes.edit.services-content.delete')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Page -->
                <div
                    class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                    v-else
                >
                    <img
                        class="h-[120px] w-[120px] p-2 dark:mix-blend-exclusion dark:invert"
                        src="{{ bagisto_asset('images/empty-placeholders/default.svg') }}"
                        alt="@lang('admin::app.settings.themes.edit.services-content.services')"
                    >

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('admin::app.settings.themes.edit.services-content.add-btn')
                        </p>
                        
                        <p class="text-gray-400">
                            @lang('admin::app.settings.themes.edit.services-content.service-info')
                        </p>
                    </div>
                </div>
            </div>

            <!-- Update Form -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form 
                    @submit="handleSubmit($event, saveServices)"
                    ref="createServiceForm"
                    enctype="multipart/form-data"
                >
                    <x-admin::modal ref="addServiceModal">
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                <template v-if="! isUpdating">
                                    @lang('admin::app.settings.themes.edit.services-content.add-btn')
                                </template>

                                <template v-else>
                                    @lang('admin::app.settings.themes.edit.services-content.update-service')
                                </template>
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <!-- Title -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.settings.themes.edit.services-content.title')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[title]"
                                    rules="required"
                                    v-model="selectedService.title"
                                    :label="trans('admin::app.settings.themes.edit.services-content.title')"
                                    :placeholder="trans('admin::app.settings.themes.edit.services-content.title')"
                                />

                                <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[title]" />
                            </x-admin::form.control-group>

                            <!-- Description -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.services-content.description')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="textarea"
                                    name="{{ $currentLocale->code }}[description]"
                                    v-model="selectedService.description"
                                    :label="trans('admin::app.settings.themes.edit.services-content.description')"
                                    :placeholder="trans('admin::app.settings.themes.edit.services-content.description')"
                                />

                                <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[description]" />
                            </x-admin::form.control-group>

                            <!-- Service Image Upload -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.services-content.service-image')
                                </x-admin::form.control-group.label>

                                <div class="flex items-center gap-4">
                                    <!-- Image Preview -->
                                    <div
                                        v-if="selectedService.image"
                                        class="relative h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700"
                                    >
                                        <img
                                            :src="selectedService.image"
                                            class="h-full w-full object-cover"
                                            :alt="selectedService.title"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white hover:bg-red-600"
                                            @click="selectedService.image = ''"
                                        >
                                            <span class="icon-delete text-xs"></span>
                                        </button>
                                    </div>

                                    <label
                                        class="secondary-button cursor-pointer"
                                        for="service-image-upload"
                                    >
                                        <span class="icon-add"></span>
                                        <span v-if="selectedService.image">
                                            @lang('admin::app.settings.themes.edit.services-content.replace-image')
                                        </span>
                                        <span v-else>
                                            @lang('admin::app.settings.themes.edit.services-content.upload-image')
                                        </span>
                                    </label>
                                    <input
                                        type="file"
                                        id="service-image-upload"
                                        class="hidden"
                                        accept="image/*"
                                        @change="uploadServiceImage($event)"
                                    />
                                </div>

                                <p class="mt-1 text-xs text-gray-500">
                                    @lang('admin::app.settings.themes.edit.services-content.image-hint')
                                </p>
                            </x-admin::form.control-group>

                            <!-- Services Icon (CSS class) -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('admin::app.settings.themes.edit.services-content.service-icon-class')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="{{ $currentLocale->code }}[service_icon]"
                                    v-model="selectedService.service_icon"
                                    :label="trans('admin::app.settings.themes.edit.services-content.service-icon-class')"
                                    :placeholder="trans('admin::app.settings.themes.edit.services-content.service-icon-placeholder')"
                                />

                                <p class="mt-1 text-xs text-gray-500">
                                    @lang('admin::app.settings.themes.edit.services-content.icon-hint')
                                </p>

                                <x-admin::form.control-group.error control-name="{{ $currentLocale->code }}[service_icon]" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="submit"
                                class="primary-button justify-center"
                                :title="trans('admin::app.settings.themes.edit.services-content.save-btn')"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-services-content', {
            template: '#v-services-content-template',

            props: ['errors'],

            data() {
                return {
                    servicesContent: @json($theme->translate($currentLocale->code)['options'] ?? null),

                    deletedServices: [],

                    selectedService: {},

                    isUpdating: false
                };
            },
            
            created() {
                if (
                    this.servicesContent == null 
                    || this.servicesContent.length == 0
                ) {
                    this.servicesContent = { services: [] };
                }  
            },

            methods: {
                saveServices(params, { resetForm, setErrors }) {
                    let formData = new FormData(this.$refs.createServiceForm);

                    if (! this.isUpdating) {
                        try {
                            this.servicesContent.services.push({
                                title: formData.get("{{ $currentLocale->code }}[title]"),
                                description: formData.get("{{ $currentLocale->code }}[description]"),
                                service_icon: formData.get("{{ $currentLocale->code }}[service_icon]") || '',
                                image: this.selectedService.image || '',
                            });

                            resetForm();
                            this.selectedService = {};
                        } catch (error) {
                            setErrors({'service_icon': [error.message]});
                        }
                        this.isUpdating = false;
                    } else {
                        // Update existing service
                        const index = this.servicesContent.services.findIndex(
                            s => s === this.selectedService
                        );
                        if (index !== -1) {
                            this.servicesContent.services[index] = {
                                ...this.servicesContent.services[index],
                                title: formData.get("{{ $currentLocale->code }}[title]"),
                                description: formData.get("{{ $currentLocale->code }}[description]"),
                                service_icon: formData.get("{{ $currentLocale->code }}[service_icon]") || '',
                                image: this.selectedService.image || '',
                            };
                        }
                        this.isUpdating = false;
                    }
                        
                    this.$refs.addServiceModal.toggle();
                },

                remove(service_details) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            this.deletedServices.push(service_details);
                    
                            this.servicesContent.services = this.servicesContent.services.filter(item => {
                                return (
                                    item.title !== service_details.title || 
                                    item.description !== service_details.description || 
                                    item.service_icon !== service_details.service_icon
                                );
                            });
                        }
                    });
                },

                add() {
                    this.selectedService = {};

                    this.isUpdating = false;

                    this.$refs.addServiceModal.toggle();
                },

                edit(service_details) {
                    this.selectedService = { ...service_details };

                    this.isUpdating = true;

                    this.$refs.addServiceModal.toggle();
                },

                uploadServiceImage($event) {
                    const file = $event.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: '@lang("admin::app.settings.themes.edit.image-upload-message")'
                        });
                        return;
                    }

                    let formData = new FormData();
                    formData.append('{{ $currentLocale->code }}[options][][image]', file);
                    formData.append('id', '{{ $theme->id }}');
                    formData.append('type', 'services_content');

                    this.$axios.post('{{ route('admin.settings.themes.store') }}', formData)
                        .then((response) => {
                            this.selectedService.image = response.data;
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

                    $event.target.value = '';
                },
            },
        });
    </script>
@endPushOnce    
