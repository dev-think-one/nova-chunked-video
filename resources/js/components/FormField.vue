<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
        <template #field>
            <template v-if="resourceId">
                <div v-if="isUploading">
                    <div>
                        <progress :value="progress" max="100" class="w-full"></progress>
                    </div>
                    <p class="pt-4 font-bold text-danger">
                        {{ __('Uploading: Do not refresh the page or edit the form before it finishes loading') }}
                    </p>
                </div>
                <template v-else>
                    <div v-if="isPreview">
                        <video controls class="w-full max-w-xl mb-4">

                            <source :src="field.previewUrl">

                            {{ __('Sorry, your browser doesn\'t support embedded videos.') }}
                        </video>
                    </div>
                    <div v-if="isPreview || isWaiting">
                        <div class="relative mb-4">
                            <input style="cursor: pointer; opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%"
                                   type="file"
                                   @change="select" :accept="field.acceptedTypes">
                            <div style="pointer-events: none; width: 100%; height: 4rem;"
                                 class="cursor-pointer focus:outline-none focus:ring rounded border-2 border-primary-300 dark:border-gray-500 hover:border-primary-500 active:border-primary-400 dark:hover:border-gray-400 dark:active:border-gray-300 bg-white dark:bg-transparent text-primary-500 dark:text-gray-400 px-3 h-9 inline-flex items-center justify-center font-bold flex-shrink-0">
                                {{ __('Click to select file from your device') }}
                            </div>
                        </div>
                        <p class="pt-4 text-danger text-sm">
                            {{
                                __('The file will start uploading immediately after adding (without waiting for the form to be submitted). Make sure you have a fast and stable internet connection before uploading.')
                            }}
                        </p>
                    </div>
                </template>
            </template>
            <div v-else>
                Uploading allowed only in edit mode
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {ref} from 'vue'
import { FormField, HandlesValidationErrors } from '../../../node_modules/laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            file: null,
            chunksCount: 0,
            chunks: [],
            uploaded: 0
        };
    },

    watch: {
        chunks: {
            deep: true,
            handler(newVal, o) {
                if (newVal.length > 0) {
                    this.upload();
                }
            }
        },
    },

    computed: {
        isWaiting() {
            return !this.field.previewUrl && !this.chunksCount && this.chunks.length <= 0;
        },
        isUploading() {
            return !this.field.previewUrl && this.chunksCount || this.chunks.length > 0;
        },
        isPreview() {
            return !!this.field.previewUrl;
        },
        progress() {
            if (this.file) {
                return Math.floor((this.uploaded * 100) / this.chunksCount);
            }
            return 0
        },
        formData() {
            let formData = new FormData;

            formData.set('is_last', this.chunks.length === 1);
            formData.set('file', this.chunks[0], `${this.file.name}.part`);

            return formData;
        },
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, this.value || '')
        },

        select(event) {
            this.file = event.target.files.item(0);
            this.createChunks();
        },

        createChunks() {
            if (this.file.size > this.field.maxSize) {
                Nova.error('File to big, please select other file')
                return;
            }
            this.chunksCount = 1;
            let chunks = Math.ceil(this.file.size / this.field.chunkSize);
            let tmpChunks = [];
            for (let i = 0; i < chunks; i++) {
                tmpChunks.push(this.file.slice(
                    i * this.field.chunkSize, Math.min(i * this.field.chunkSize + this.field.chunkSize, this.file.size),
                    this.file.type
                ));
            }

            this.chunksCount = tmpChunks.length;
            this.chunks = tmpChunks;
        },
        upload() {
            Nova.request()
                .post(
                    `/nova-vendor/nova-chunked/video-upload/${this.resourceName}/${this.resourceId}/${this.field.attribute}`,
                    this.formData,
                    {
                        headers: {
                            'Content-Type': 'application/octet-stream'
                        },
                    }
                )
                .then(response => {
                    this.chunks.shift();
                    this.uploaded++;
                    if (this.chunks.length <= 0) {
                        // load info
                        this.chunksCount = 0;
                    }
                    if (response.data && response.data.video_url) {
                        this.field.previewUrl = response.data.video_url;
                    }
                })
                .catch(() => {
                    this.file = null;
                    this.chunksCount = 0;
                    this.chunks = [];
                    this.uploaded = 0;

                    Nova.error(this.__('Upload error, reload page and try again'))
                })
        },
    },
}
</script>

<style scoped>
progress::-moz-progress-bar {
    background: #4099de;
}

progress::-webkit-progress-value {
    background: #4099de;
}

progress {
    color: #4099de;
}
</style>
