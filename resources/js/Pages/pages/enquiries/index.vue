<script>
import { ref, onMounted, watch, computed } from 'vue';
import { Link, Head, useForm } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
import axios from 'axios';
import Multiselect from "@vueform/multiselect";
import "@vueform/multiselect/themes/default.css";

export default {
    components: {
        Layout,
        PageHeader,
        Link,
        Head,
        Multiselect,
    },
    computed: {
        StatusOptions() {
            return [
                { label: 'Pending', value: 'pending' },
                { label: 'Answered', value: 'answered' },
            ];
        },
    },
    setup(props) {
        const searchQuery = ref('');
        const perPage = ref(10);
        const currentPage = ref(1);
        const rows = ref(0);
        const paginator = ref({});
        const commentModal = ref(false);
        const selecetedItem = ref(null);

        const enquiries = ref([]);

        const form =useForm({
            comment: '',
            status: '',
        })

        const openCommentModal = (item) => {
            commentModal.value = true;
            selecetedItem.value = item
            form.comment = item.comment;
            form.status = item.status;
        };

        // Parse images for display
        const images = computed(() => {
            try {
                return JSON.parse(props.property?.images || '[]');
            } catch {
                return [];
            }
        });

        const fetchData = async () => {

            try {
                const response = await axios.get('/enquiries/fetchdata', {
                    params: {
                        page: currentPage.value,
                        perPage: perPage.value,
                    },
                });
                enquiries.value = response.data.enquiries.data;
                paginator.value = response.data.enquiries;
                rows.value = response.data.enquiries.total;
            } catch (error) {
                console.log(error);
            }
        };

        const handleSubmit = async() => {
            form.errors = {};
            if(!form.status) { form.errors.status = "Please select the Status"; return}
            try {
                const response = await axios.put(`/enquiries/${selecetedItem.value?.id}`, form)
                if(response.status == 200) {
                    fetchData();
                    commentModal.value = false;
                }
            } catch(error){
                console.log(error)
            }
        }

        onMounted(fetchData);

        watch(currentPage, () => {
            fetchData();
        });

        return {
            currentPage,
            rows,
            perPage,
            searchQuery,
            enquiries,
            fetchData,
            images,commentModal,openCommentModal, form, handleSubmit
        };
    },
    methods: {
        ucfirst(value) {
            if (!value) return '';
            return value.charAt(0).toUpperCase() + value.slice(1);
        }
    }
};
</script>

<template>
    <Layout>

        <Head title="Enquiry Details"/>
        <PageHeader title="Enquiry Details" pageTitle="Home" />
        <BRow>
            <BCol lg="12">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex" >
                                <BCol lg="6">
                                    <div class="d-flex gap-3" >
                                        <input v-model="searchQuery" @input="fetchData" type="text" class="form-control"
                                            placeholder="Search enquiries..." />
                                    </div>
                                </BCol>
                            </div>
                            <div class="card-body">
                                <div class="listjs-table" >
                                    <div class="table-responsive table-card mt-3 mb-1">
                                            <table class="table align-middle table-hover step-row ">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Phone</th>
                                                    <th>Message</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="list form-check-all">
                                                <tr v-for="(item, index) in enquiries" :key="item.id"
                                                    @click="openCommentModal(item)">
                                                    <td>{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                                    <td>{{ item.name }}</td>
                                                    <td>{{ item.email }}</td>
                                                    <td>{{ item.phone }}</td>
                                                    <td>{{ item.message }}</td>
                                                    <td>
                                                        <span class="bg-warning-subtle badge fs-6 text-warning"
                                                            v-if="item.status === 'pending'">
                                                            {{ ucfirst(item.status) }}
                                                        </span>
                                                        <span class="bg-success-subtle badge fs-6 text-success"
                                                            v-if="item.status === 'answered'">
                                                            {{ ucfirst(item.status) }}
                                                        </span>
                                                        <span class="bg-danger-subtle badge fs-6 text-danger"
                                                            v-if="item.status === 'resolved'">
                                                            {{ ucfirst(item.status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div v-if="enquiries.length == 0">
                                            <h5 class="text-center mt-4">No results found</h5>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                      <p class="mb-0"><b>Total Items: {{ rows }}</b></p>
                                      <b-pagination
                                        v-model="currentPage"
                                        :total-rows="rows"
                                        :per-page="perPage"
                                      ></b-pagination>
                                    </div>


                                </div>
                            </div><!-- end card -->
                        </div>
                        <!-- end col -->
                    </div>
                </div>
            </BCol>
        </BRow>
        <BModal v-model="commentModal" hide-footer centered title="Add Comment">
            <form @submit.prevent="handleSubmit">
                <!-- Comment Input -->
                <div class="mb-3">
                    <label class="form-label">Comment:</label>
                    <textarea v-model="form.comment" class="form-control" rows="3"
                        placeholder="Enter your comment" >
                    </textarea>
                </div>

                <!-- Status Input -->
                <div class="mb-3">
                    <label  class="form-label">Status:</label>
                    <Multiselect v-model="form.status" :close-on-select="true" placeholder="Select Status" searchable :options="StatusOptions" />
                    <p class="text-danger" v-if="form.errors?.status">{{ form.errors.status }}</p>

                </div>

                <!-- Submit Button -->
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </BModal>

    </Layout>
</template>
