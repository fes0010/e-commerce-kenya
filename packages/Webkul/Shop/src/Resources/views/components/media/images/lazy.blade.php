<v-shimmer-image {{ $attributes }}>
    <div {{ $attributes->merge(['class' => 'shimmer']) }}></div>
</v-shimmer-image>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-shimmer-image-template"
    >
        <div
            :id="'image-shimmer-' + $.uid"
            class="shimmer"
            v-bind="$attrs"
            v-if="isLoading"
        >
        </div>

        <img
            v-bind="$attrs"
            :src="src"
            :srcset="srcset"
            :sizes="sizes"
            :id="'image-' + $.uid"
            @load="onLoad"
            @@error="onLoad"
            v-show="! isLoading"
            :loading="lazy ? 'lazy' : 'eager'"
            decoding="async"
        >
    </script>

    <script type="module">
        app.component('v-shimmer-image', {
            template: '#v-shimmer-image-template',

            props: {
                lazy: {
                    type: Boolean,
                    default: true,
                },

                src: {
                    type: String,
                    default: '',
                },
                
                srcset: {
                    type: String,
                    default: '',
                },
                
                sizes: {
                    type: String,
                    default: '',
                },
            },

            data() {
                return {
                    isLoading: true,
                };
            },

            mounted() {
                // If the image is already complete (cached), reveal it instantly
                this.$nextTick(() => {
                    let img = document.getElementById('image-' + this.$.uid);
                    if (img && img.complete && img.naturalHeight !== 0) {
                        this.isLoading = false;
                    }
                });
            },

            methods: {
                onLoad() {
                    this.isLoading = false;
                },
            },
        });
    </script>
@endPushOnce
