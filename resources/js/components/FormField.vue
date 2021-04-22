<template>
    <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
        <template slot="field">
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
                        <video controls class="w-full max-w-md">

                            <source :src="field.previewUrl">

                            {{ __('Sorry, your browser doesn\'t support embedded videos.') }}
                        </video>
                    </div>
                    <div v-if="isPreview || isWaiting">
                        <div class="relative">
                            <input class="w-full opacity-0" style="height: 4rem" type="file"
                                   @change="select" :accept="field.acceptedTypes">
                            <div style="top: 0; left: 0"
                                 class="form-file-btn btn btn-default btn-primary select-none absolute top-left h-full flex text-center justify-center items-center p-4 pointer-events-none">
                                {{ __('Click to select file from you device') }}
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
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

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
        chunks(n, o) {
            if (n.length > 0) {
                this.upload();
            }
        }
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
                this.$toasted.show('File to big, please select other file', {type: 'error'})
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

                    this.$toasted.show(this.__('Upload error, reload page and try again'), {type: 'error',})
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
