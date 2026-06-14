<div class="1180:hidden flex flex-col gap-4 max-w-full overflow-hidden">
    <!-- Swipeable Main Image/Video Gallery with Prev/Next arrows -->
    <div class="relative w-full max-w-full overflow-hidden">
        <!-- Prev Arrow -->
        <button
            v-if="[...media.images, ...media.videos].length > 1 && activeIndex > 0"
            class="absolute left-2 top-1/2 -translate-y-1/2 z-10 flex items-center justify-center w-8 h-8 rounded-full bg-black/50 text-white text-lg icon-arrow-left transition-opacity hover:bg-black/70"
            @click="scrollToMedia(activeIndex - 1)"
            aria-label="Previous image"
        ></button>

        <!-- Next Arrow -->
        <button
            v-if="[...media.images, ...media.videos].length > 1 && activeIndex < [...media.images, ...media.videos].length - 1"
            class="absolute right-2 top-1/2 -translate-y-1/2 z-10 flex items-center justify-center w-8 h-8 rounded-full bg-black/50 text-white text-lg icon-arrow-right transition-opacity hover:bg-black/70"
            @click="scrollToMedia(activeIndex + 1)"
            aria-label="Next image"
        ></button>

        <div
            ref="mobileSwiper"
            class="flex overflow-x-auto snap-x snap-mandatory scroll-smooth w-full max-w-full aspect-square rounded-xl scrollbar-hide"
            @scroll="onMobileScroll"
        >
            <div
                v-for="(mediaItem, index) in [...media.images, ...media.videos]"
                :key="index"
                class="w-full shrink-0 snap-center aspect-square max-w-full"
            >
                <img
                    v-if="mediaItem.type !== 'videos'"
                    class="w-full h-full object-cover cursor-pointer rounded-xl aspect-square block"
                    :src="mediaItem.large_image_url"
                    alt="{{ $product->name }}"
                    @click="isImageZooming = !isImageZooming"
                />

                <div
                    v-else
                    class="w-full h-full cursor-pointer rounded-xl aspect-square"
                >
                    <video
                        controls
                        class="w-full h-full object-cover rounded-xl"
                        alt="{{ $product->name }}"
                        @click="isImageZooming = !isImageZooming"
                    >
                        <source
                            :src="mediaItem.video_url"
                            type="video/mp4"
                        />
                    </video>
                </div>
            </div>
        </div>

        <!-- Dot indicators -->
        <div
            class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5 bg-black/30 backdrop-blur-sm px-2.5 py-1.5 rounded-full"
            v-if="[...media.images, ...media.videos].length > 1"
        >
            <span
                v-for="(mediaItem, index) in [...media.images, ...media.videos]"
                :key="index"
                class="w-2 h-2 rounded-full cursor-pointer transition-all duration-300"
                :class="activeIndex === index ? 'bg-white w-4' : 'bg-white/50'"
                @click="scrollToMedia(index)"
            ></span>
        </div>
    </div>

    <!-- Thumbnails below -->
    <div class="flex gap-2.5 overflow-x-auto scrollbar-hide w-full pb-2" v-if="[...media.images, ...media.videos].length > 1">
        <template v-for="(mediaItem, index) in [...media.images, ...media.videos]">
            <video
                v-if="mediaItem.type == 'videos'"
                :class="`h-[70px] w-[70px] min-w-[70px] object-cover cursor-pointer rounded-xl border ${isActiveMedia(index) ? 'pointer-events-none border-navyBlue border-2' : 'border-zinc-200'}`"
                @click="scrollToMedia(index)"
                alt="{{ $product->name }}"
                tabindex="0"
            >
                <source
                    :src="mediaItem.video_url"
                    type="video/mp4"
                />
            </video>

            <img
                v-else
                :class="`h-[70px] w-[70px] min-w-[70px] object-cover cursor-pointer rounded-xl border ${isActiveMedia(index) ? 'pointer-events-none border-navyBlue border-2' : 'border-zinc-200'}`"
                :src="mediaItem.small_image_url"
                alt="{{ $product->name }}"
                width="70"
                height="70"
                tabindex="0"
                @click="scrollToMedia(index)"
            />
        </template>
    </div>
</div>
