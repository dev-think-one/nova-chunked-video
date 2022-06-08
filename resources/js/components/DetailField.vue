<template>
    <PanelItem :index="index" :field="field">
        <template #value>
            <video v-if="field.previewUrl" controls class="w-full max-w-md">

                <source :src="field.previewUrl"
                        type="video/mp4">

                {{ __('Sorry, your browser doesn\'t support embedded videos.') }}
            </video>
            <div v-else>
                -
            </div>
        </template>
    </PanelItem>
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],
}
</script>
