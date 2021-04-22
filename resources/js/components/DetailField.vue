<template>
    <panel-item :field="field">
        <template slot="value">
            <video v-if="field.previewUrl" controls class="w-full max-w-md">

                <source :src="field.previewUrl"
                        type="video/mp4">

                {{ __('Sorry, your browser doesn\'t support embedded videos.') }}
            </video>
            <div v-else>
                -
            </div>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
}
</script>
