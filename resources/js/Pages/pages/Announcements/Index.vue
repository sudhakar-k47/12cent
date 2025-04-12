<script>
import { ref, onMounted, watch } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import Swal from "sweetalert2";
import axios from 'axios';
import { useForm, router } from '@inertiajs/vue3';
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";
import CKEditor from '@ckeditor/ckeditor5-vue';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

export default {
    components: {
        Layout,
        PageHeader,
        Link,
        Head,Multiselect,
        ckeditor: CKEditor.component,flatPickr
    },
    setup() {
        const Modal = ref(false);
        const searchQuery = ref('');
        const perPage = ref(10);
        const currentPage = ref(1);
        const rows = ref(0);
        const paginator = ref({});

        const announcements = ref([]);
        const form = useForm({
            title: '',
            date: '',
            description: '',
            type: '',
            is_active: '1',
            errors: {},
        });
        const selectedItem = ref(null);

        const fetchData = async () => {
            try {
                const response = await axios.get('/announcements/fetchdata', {
                    params: {
                        page: currentPage.value,
                        perPage: perPage.value,
                        searchQuery: searchQuery.value,
                    },
                });
                announcements.value = response.data.announcements.data;
                paginator.value = response.data.announcements;
                rows.value = response.data.announcements.total;
            } catch (error) {
                console.log(error);
            }
        };

        onMounted(fetchData);

        const createData = () => {
            form.reset();
            selectedItem.value = null;
            Modal.value = true;
        };

        const editData = (item) => {
            selectedItem.value = item;
            form.title = item.title;
            form.date = item.date;
            form.description = item.description;
            form.type = item.type;
            form.is_active = item.is_active == 1 ? '1' : '0';
            Modal.value = true;
        };

        const deleteItem = async (item) => {
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete the property: ${item.title}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        await axios.delete(`/announcements/${item.id}`);
                        Swal.fire('Deleted!', 'The property has been deleted.', 'success');
                    } catch (error) {
                        Swal.fire('Error', 'Failed to delete the property.', 'error');
                    }
                }
            });
        };

        const handleValidation = () => {
            form.errors = {};
            const requiredFields = {
                title: 'Please enter the title',
                date: 'Select the Ddte',
                description: 'Please enter the description',
                type: 'Please enter the type',
            };

            Object.keys(requiredFields).forEach((field) => {
                if (!form[field]) {
                    form.errors[field] = requiredFields[field];
                }
            });

            return Object.keys(form.errors).length === 0;
        };

        const handleSubmit = async () => {
            form.errors = {};

            if (!handleValidation()) {
                return;
            }
            try {
                let response;
                if(selectedItem.value){
                    response = await axios.put(`/announcements/${selectedItem.value}`, form.data());
                }else {
                    response = await axios.post('/announcements', form.data());
                }
                if (response.status === 200 || response.status === 201) {
                    fetchData();
                    Modal.value = false;
                }
            } catch(error){
                console.log(error)
            }
        };


        watch(currentPage, () => {
            fetchData();
        });

        return {
            currentPage, rows, perPage, searchQuery,
            announcements, Modal, form,
            createData, editData, deleteItem,
            handleSubmit, fetchData
        };
    },
    methods: {
        truncate(text, length) {
            if (text.length > length) {
                return text.slice(0, length) + "...";
            }
            return text;
        },
    },
    data() {
        return {
            editor: ClassicEditor
        };
    },
    computed: {
        TypeOptions() {
            return [
                { label: 'Meeting', value: 'Meeting' },
                { label: 'Event', value: 'Event' },
            ];
        },
        isActiveOptions() {
            return [
                { label: 'Active', value: '1' },
                { label: 'Inactive', value: '0  ' },
            ];
        }
    },
};
</script>



<template>
    <Layout>

        <Head title="announcements" />
        <PageHeader title="announcements" pageTitle="Manage announcements" />
        <BRow>
            <BCol lg="12">
                <BCard>
                    <BCardHeader>
                        <div class="d-flex justify-content-between">
                            <div class="col-sm-4 search-box flex-grow ">
                                <input type="text" class="form-control search bg-light border-light w-100"
                                    placeholder="Search event, meeting, description..."
                                    @keyup.enter="fetchData" v-model="searchQuery" />
                                <i class="ri-search-line search-icon mx-1"></i>
                            </div>
                            <div>
                                <button class="btn btn-primary" @click="createData">Create</button>
                            </div>
                        </div>
                    </BCardHeader>
                    <BCardBody>
                        <div class="table-responsive">
                            <table class="table align-middle table-hover step-row ">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in announcements" :key="item.id">
                                        <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                        <td>{{ item.title }}</td>
                                        <td v-html="truncate(item.description, 100)"></td>
                                        <td>{{ item.date }}</td>
                                        <td>{{ item.type }}</td>
                                        <td>
                                            <span v-if="item.is_active == '1'" class="badge bg-success">Active</span>
                                            <span v-else class="badge bg-danger">Inactive</span>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <button class="btn btn-sm btn-secondary"
                                                    @click.stop="editData(item)"><i
                                                        class='bx bx-edit-alt'></i></button>
                                                <button class="btn btn-sm btn-danger mx-2"
                                                    @click.stop="deleteItem(item)"><i
                                                        class='bx bxs-trash'></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="announcements.length == 0">
                                <h5 class="text-center mt-4">No results found</h5>
                            </div>
                        </div>
                    </BCardBody>
                    <div class="d-flex justify-content-between">
                        <div>
                            <p><b>Total Items: {{ rows }}</b></p>
                        </div>
                        <b-pagination v-model="currentPage" :total-rows="rows" :per-page="perPage"
                            align="right"></b-pagination>
                    </div>
                </BCard>
            </BCol>
        </BRow>


        <BModal v-model="Modal" title="Property Form" class="modal-lg" hide-footer
            header-class="bg-secondary-subtle p-4">
            <form @submit.prevent="handleSubmit" enctype="multipart/form-data">
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" v-model="form.title" class="form-control" id="title"
                            :class="{ 'is-invalid': form.errors.title && !form.title }" />
                        <div class="invalid-feedback">{{ form.errors.title }}</div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="location" class="form-label">Date <span class="text-danger">*</span></label>
                        <flat-pickr v-model="form.date" class="form-control input-sm"
                                            :config="{ dateFormat: 'Y-m-d' }" :class="{ 'is-invalid': form.errors.date && !form.date }" />

                                            <div class="invalid-feedback">{{ form.errors.date }}</div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description <span
                                class="text-danger">*</span></label>
                        <ckeditor v-model="form.description" :editor="editor" :class="{ 'border-danger': form.errors.description && !form.description }"></ckeditor>
                        <div class="text-danger" v-if="form.errors?.description && !form.description">{{ form.errors.description }}</div>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="type" class="form-label">Type <span
                                class="text-danger">*</span></label>
                        <Multiselect v-model="form.type" :close-on-select="true"
                            placeholder="Select Type" searchable :options="TypeOptions"
                            :class="{ 'border-danger': form.errors.type && !form.type }" />
                        <div class="invalid-feedback">{{ form.errors.type }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="is_active" class="form-label">Status <span class="text-danger">*</span></label>
                        <Multiselect v-model="form.is_active" :close-on-select="true" placeholder="Select Status"
                            searchable :options="isActiveOptions" />
                    </div>

                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </BModal>
    </Layout>
</template>

<style>
.border-danger {
    border: 1px solid red !important;
}
</style>
