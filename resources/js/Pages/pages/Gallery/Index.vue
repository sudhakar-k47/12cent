<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import Layout from '@/Layouts/main.vue';
import PageHeader from '@/Components/page-header.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";

const props = defineProps({
    galleries: Object,
});

const modal = ref(false);
const selected = ref(null);
const search = ref('');
const currentPage = ref(1);

const form = useForm({
    title: '',
    description: '',
    type: '',
    images: [],
    existing_images: [],
    is_active: '1',
});

const typeOptions = computed(() => [
    { label: 'Meeting', value: 'Meeting' },
    { label: 'Event', value: 'Event' },
]);

const filteredGalleries = computed(() => {
    if (!search.value) return props.galleries.data;

    return props.galleries.data.filter(item =>
        item.title.toLowerCase().includes(search.value.toLowerCase()) ||
        (item.description && item.description.toLowerCase().includes(search.value.toLowerCase()))
    );
});

const openModal = (item = null) => {
    form.reset();
    selected.value = item;

    if (item) {
        form.title = item.title;
        form.description = item.description || '';
        form.type = item.type;
        form.is_active = item.is_active ? '1' : '0';
        form.existing_images = item.images ?? [];
    } else {
        form.existing_images = [];
    }

    modal.value = true;
};

const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);
    form.images = files;
};

const imagePreview = (image) => {
    if (typeof image === 'string') {
        return image;
    }
    return URL.createObjectURL(image);
};

const removeImage = (index, isExisting) => {
    if (isExisting) {
        form.existing_images.splice(index, 1);
    } else {
        form.images.splice(index, 1);
    }
};

const handleSubmit = async () => {
    try {
        const payload = new FormData();
        payload.append('title', form.title);
        payload.append('description', form.description || '');
        payload.append('type', form.type);
        payload.append('is_active', form.is_active);

        // Send existing_images properly as array
        form.existing_images.forEach((image) => {
            payload.append('existing_images[]', image);
        });

        // Attach new images
        form.images.forEach((file) => {
            payload.append('images[]', file);
        });

        if (selected.value) {
            payload.append('_method', 'PUT');
            await axios.post(`/galleries/${selected.value.id}`, payload);
        } else {
            await axios.post('/galleries', payload);
        }

        modal.value = false;
        router.visit('/galleries', { preserveState: false });
    } catch (error) {
        console.error(error);
    }
};


const deleteItem = (item) => {
    Swal.fire({
        title: 'Delete this gallery item?',
        text: item.title,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/galleries/${item.id}`);
                router.visit('/galleries', { preserveState: false });
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Failed to delete the gallery item', 'error');
            }
        }
    });
};


const truncate = (text, length) => {
    if (text.length > length) {
        return text.slice(0, length) + "...";
    }
    return text;
};
</script>

<template>
    <Layout>

        <Head title="Gallery" />
        <PageHeader title="Gallery" pageTitle="Manage Gallery" />

        <div class="d-flex justify-content-between mb-3">
            <input v-model="search" class="form-control w-25" placeholder="Search gallery..." />
            <button class="btn btn-primary" @click="openModal()">Create Gallery</button>
        </div>

        <div class="row">
            <div class="col-md-3 mb-4" v-for="item in filteredGalleries" :key="item.id">
                <div class="card h-100 shadow-sm rounded-3 overflow-hidden cursor-pointer" @click="openModal(item)">
                    <div class="ratio ratio-4x3">
                        <img
                            :src="item.images?.[0]"
                            class="object-fit-cover w-100 h-100"
                            alt="gallery image"
                            @error="event.target.src='https://via.placeholder.com/300x225?text=No+Image'"
                        />
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title text-truncate">{{ truncate(item.title, 100) }}</h5>
                            <span class="badge" :class="item.is_active ? 'bg-success' : 'bg-secondary'">
                                {{ item.is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="badge mx-2 bg-info text-white">{{ item.type }}</span>
                            <p class="card-text small text-muted" style="min-height: 3em;">{{ truncate(item.description, 100) }}</p>
                        </div>
                        <div>

                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-sm btn-outline-danger" @click="deleteItem(item)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredGalleries.length === 0" class="col-12 text-center py-5">
                <p class="text-muted">No gallery items found</p>
            </div>
        </div>

        <!-- Modal -->

        <BModal size="lg" :title="selected ? 'Edit Gallery' : 'Create Gallery'" v-model="modal" hide-footer>
            <div >
                <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input v-model="form.title" class="form-control" required />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <Multiselect v-model="form.type" :options="typeOptions" :searchable="true"
                            :close-on-select="true" placeholder="Select Type"
                            :class="{ 'is-invalid': form.errors.type }" required />
                        <div v-if="form.errors.type" class="invalid-feedback">
                            {{ form.errors.type }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Upload Images</label>
                        <input type="file" multiple @change="handleImageUpload" class="form-control" id="images"
                            accept="image/jpeg,image/png,image/jpg"
                            :required="!selected || form.existing_images.length === 0" />
                        <small class="text-muted">Supported formats: JPG, JPEG, PNG. Max size: 5MB per
                            image</small>
                    </div>

                    <div class="mb-3">
                        <div v-if="form.existing_images.length > 0" class="mb-2">
                            <h6 class="text-muted">Existing Images</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <div v-for="(image, index) in form.existing_images" :key="'existing-' + index"
                                    class="position-relative">
                                    <img :src="image" class="rounded" width="100" height="100"
                                        style="object-fit: cover;" />
                                    <button type="button"
                                        class="btn-close position-absolute top-0 end-0 bg-white"
                                        @click="removeImage(index, true)"></button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.images.length > 0" class="mb-2">
                            <h6 class="text-muted">New Images</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <div v-for="(image, index) in form.images" :key="'new-' + index"
                                    class="position-relative">
                                    <img :src="imagePreview(image)" class="rounded" width="100" height="100"
                                        style="object-fit: cover;" />
                                    <button type="button"
                                        class="btn-close position-absolute top-0 end-0 bg-white"
                                        @click="removeImage(index, false)"></button>
                                </div>
                            </div>
                        </div>

                        <div v-if="form.existing_images.length === 0 && form.images.length === 0"
                            class="text-center p-3 border rounded">
                            <i class="ri-image-add-line fs-2 text-muted"></i>
                            <p class="text-muted mb-0">No images uploaded yet</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select v-model="form.is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2"
                            @click="modal = false">Cancel</button>
                        <button class="btn btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </BModal>
    </Layout>
</template>
