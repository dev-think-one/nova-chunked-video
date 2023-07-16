<template>
    <PanelItem
        :index="index"
        :field="field"
    >
        <template #value>
            <template v-if="shouldShowVideo">
                <video
                    controls
                    :src="src"
                    :autoplay="autoplay"
                    :poster="imageUrl"
                />
            </template>

            <span v-else>&mdash;</span>
            <p
                v-if="field.downloadable"
                class="flex items-center text-sm mt-3"
            >
                <a
                    v-if="field.downloadable"
                    :dusk="field.attribute + '-download-link'"
                    tabindex="0"
                    class="cursor-pointer dim btn btn-link text-primary inline-flex items-center"
                    @keydown.enter.prevent="download"
                    @click.prevent="download"
                >
                    <icon
                        class="mr-2"
                        type="download"
                        view-box="0 0 24 24"
                        width="16"
                        height="16"
                    />
                    <span class="class mt-1">{{ __('Download') }}</span>
                </a>
            </p>
        </template>
    </PanelItem>
</template>

<script>
import download from "../functions/download";

export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
    data() {
        return {
            src: this.field.previewUrl,
            autoplay: false,
        };
    },
    computed: {
        shouldShowVideo() {
            return Boolean(this.src);
        },
        imageUrl() {
            return this.field.thumbnailUrl;
        },
    },
    methods: {
        download() {
            download(this.resourceName, this.resourceId, this.field.attribute)
        },
    },
};
</script>

<style lang="scss" scoped>
video {
    width: 500px;
    max-width: 100%;
    object-fit: cover;
    outline: none;
    border: none;
}
</style>

