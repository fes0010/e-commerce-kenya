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
            :data-src="src"
            :data-srcset="srcset"
            :data-sizes="sizes"
            :id="'image-' + $.uid"
            @load="onLoad"
            v-show="! isLoading"
            v-if="lazy"
            decoding="async"
        >

        <img
            v-bind="$attrs"
            :src="src"
            :srcset="srcset"
            :sizes="sizes"
            :id="'image-' + $.uid"
            @load="onLoad"
            v-else
            v-show="! isLoading"
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
                let self = this;

                if (! this.lazy) {
                    return;
                }

                let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            let lazyImage = document.getElementById('image-' + self.$.uid);

                            if (lazyImage.dataset.src) {
                                lazyImage.src = lazyImage.dataset.src;
                            }
                            if (lazyImage.dataset.srcset) {
                                lazyImage.srcset = lazyImage.dataset.srcset;
                            }
                            if (lazyImage.dataset.sizes) {
                                lazyImage.sizes = lazyImage.dataset.sizes;
                            }

                            lazyImageObserver.unobserve(lazyImage);
                        }
                    });
                });

                lazyImageObserver.observe(document.getElementById('image-shimmer-' + this.$.uid));
            },

            methods: {
                onLoad() {
                    this.isLoading = false;
                },
            },
        });
    </script>
@endPushOnce
