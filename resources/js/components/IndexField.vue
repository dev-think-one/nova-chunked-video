<template>
    <p>
        <video
            v-if="src"
            class="block"
            controls
            :src="src"
            :poster="poster"
            :autoplay="autoplay"
            :preload="preload"
        />
        <span v-else>&mdash;</span>
    </p>
</template>

<script>
export default {
    props: ['viaResource', 'viaResourceId', 'resourceName', 'field'],
    data() {
        return {
            src: this.field.previewUrl,
            poster: this.field.thumbnailUrl,
            autoplay: null,
            preload: 'none',
        };
    },
};
</script>

