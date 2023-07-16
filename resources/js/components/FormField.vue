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

                    <div
                        v-if="videoUrl"
                        class="video-wrapper inline-flex box-content border border-gray-200 dark:border-gray-700 rounded-lg divide-x divide-gray-200 dark:divide-gray-700 overflow-hidden">
                        <div>
                            <video
                                controls
                                controlsList="nodownload"
                                :autoplay="false"
                                class="flex-grow"
                                :poster="imageUrl"
                            >
                                <source :src="videoUrl">

                                {{ __('Sorry, your browser doesn\'t support embedded videos.') }}
                            </video>
                        </div>
                        <div class="flex-none flex flex-col divide-y divide-gray-200 dark:divide-gray-700">
                            <a
                                v-if="shouldShowRemoveButton"
                                :dusk="currentField.attribute + '-delete-link'"
                                type="button"
                                class="cursor-pointer group-control flex items-center justify-center w-8 h-8"
                                :title="__('Delete')"
                                @click.prevent="confirmRemoval"
                            >
                                <Icon
                                    type="trash"
                                    width="16"
                                    height="16"
                                />
                            </a>
                            <a
                                :href="videoUrl"
                                target="_blank"
                                :dusk="currentField.attribute + '-open-link'"
                                type="button"
                                class="cursor-pointer group-control flex items-center justify-center w-8 h-8"
                                :title="__('Open')"
                            >
                                <Icon
                                    type="external-link"
                                    width="16"
                                    height="16"
                                />
                            </a>
                            <a v-if="currentField.downloadable"
                               target="_blank"
                               :dusk="currentField.attribute + '-download-link'"
                               type="button"
                               class="cursor-pointer group-control flex items-center justify-center w-8 h-8"
                               :title="__('Download')"
                               @keydown.enter.prevent="download"
                               @click.prevent="download"
                            >
                                <Icon
                                    type="download"
                                    width="16"
                                    height="16"
                                />
                            </a>
                        </div>
                        <ConfirmUploadRemovalModal
                            :show="removeModalOpen"
                            @confirm="removeFile"
                            @close="closeRemoveModal"
                        />
                    </div>

                    <div v-if="!isReadonly && (videoUrl || isWaiting)">
                        <div class="relative mb-4">
                            <input
                                style="cursor: pointer; opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%"
                                type="file"
                                @change="select" :accept="currentField.acceptedTypes">
                            <DefaultButton
                                type="button"
                                tabindex="0"
                                class="pointer-events-none w-full flex-shrink-0"
                            >{{ __('Click to select file from your device') }}
                            </DefaultButton>
                        </div>
                        <p class="text-red-500 font-semibold text-sm">
                            {{
                                __('The file will start uploading immediately after adding (without waiting for the form to be submitted). Make sure you have a fast and stable internet connection before uploading.')
                            }}
                        </p>
                    </div>

                    <p
                        v-if="hasError"
                        class="text-xs mt-2 text-danger"
                    >
                        {{ firstError }}
                    </p>
                </template>
            </template>
            <div v-else>
                Uploading allowed only in edit mode
            </div>
        </template>
    </DefaultField>
</template>

<script>
import {DependentFormField, HandlesValidationErrors, Errors} from 'laravel-nova';
import download from "../functions/download";

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    data() {
        return {
            removeModalOpen: false,
            file: null,
            chunksCount: 0,
            chunks: [],
            uploaded: 0,
            uploadErrors: new Errors(),
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
        /**
         * Determine if the field has an upload error.
         */
        hasError() {
            return this.uploadErrors.has(this.fieldAttribute);
        },

        /**
         * Return the first error for the field.
         */
        firstError() {
            if (this.hasError) {
                return this.uploadErrors.first(this.fieldAttribute);
            }
        },

        /**
         * Determine whether the file field input should be editable.
         */
        isReadonly() {
            return Boolean(!!this.currentField.readonly);
        },

        /**
         * Determine whether the field should show the remove button.
         */
        shouldShowRemoveButton() {
            return Boolean(this.currentField.deletable && !this.isReadonly);
        },

        /**
         * Return the preview URL for the field.
         */
        videoUrl() {
            return this.currentField.previewUrl;
        },

        isWaiting() {
            return !this.videoUrl && !this.chunksCount && this.chunks.length <= 0;
        },
        isUploading() {
            return !this.videoUrl && this.chunksCount || this.chunks.length > 0;
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
            this.value = this.currentField.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.currentField.attribute, this.value || '')
        },

        select(event) {
            this.file = event.target.files.item(0);
            this.createChunks();
        },

        /**
         * Confirm removal of the linked file
         */
        confirmRemoval() {
            this.removeModalOpen = true;
        },
        /**
         * Close the upload removal modal
         */
        closeRemoveModal() {
            this.removeModalOpen = false;
        },

        createChunks() {
            if (this.file.size > this.currentField.maxSize) {
                Nova.error('File to big, please select other file')
                return;
            }
            this.chunksCount = 1;
            let chunks = Math.ceil(this.file.size / this.currentField.chunkSize);
            let tmpChunks = [];
            for (let i = 0; i < chunks; i++) {
                tmpChunks.push(this.file.slice(
                    i * this.currentField.chunkSize, Math.min(i * this.currentField.chunkSize + this.currentField.chunkSize, this.file.size),
                    this.file.type
                ));
            }

            this.chunksCount = tmpChunks.length;
            this.chunks = tmpChunks;
        },
        upload() {
            Nova.request()
                .post(
                    `/nova-vendor/nova-chunked/video-upload/${this.resourceName}/${this.resourceId}/${this.currentField.attribute}`,
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
                        this.currentField.previewUrl = response.data.video_url;
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
        /**
         * Remove the linked file from storage
         */
        async removeFile() {
            this.uploadErrors = new Errors();
            const {
                resourceName,
                resourceId,
                relatedResourceName,
                relatedResourceId,
                viaRelationship,
            } = this;
            const {attribute} = this.currentField;
            const uri = this.viaRelationship
                ? `/nova-api/${resourceName}/${resourceId}/${relatedResourceName}/${relatedResourceId}/field/${attribute}?viaRelationship=${viaRelationship}`
                : `/nova-api/${resourceName}/${resourceId}/field/${attribute}`;
            try {
                await Nova.request().delete(uri);
                this.closeRemoveModal();
                this.$emit('file-deleted');
                Nova.success(this.__('The file was deleted!'));
                this.currentField.previewUrl = null;
            } catch (error) {
                this.closeRemoveModal();
                if (error.response.status == 422) {
                    this.uploadErrors = new Errors(error.response.data.errors);
                }
            }
        },

        download() {
            download(this.resourceName, this.resourceId, this.currentField.attribute)
        },
    },
}
</script>

<style scoped lang="scss">
progress {
    color: rgba(var(--colors-primary-500));
    border-radius: 5px;
    border: 2px solid;
    border-color: rgba(var(--colors-gray-200));
}

progress::-webkit-progress-bar {
    border-radius: 5px;
    background-color: rgba(var(--colors-gray-50));
    border: 1px solid;
    border-color: rgba(var(--colors-gray-200));
}

progress::-webkit-progress-value {
    background-color: rgba(var(--colors-primary-500));
    border-radius: 5px;
}

</style>
