<script>
import { Link, Head } from '@inertiajs/vue3';
import Layout from "@/Layouts/main.vue";
import PageHeader from "@/Components/page-header.vue";
export default {
    components: {
        Layout,
        PageHeader,
        Link, Head
    },

    methods: {
        createForm() {
            this.modalForm = true;
        }
    },

    data() {
        return {
            modalForm: false,
        }
    }
};
</script>

<template>
    <Layout>

        <Head title="Status" />
        <PageHeader title="Status" pageTitle="Pages" />
        <BRow>
            <BCol lg="12">
                <BCard no-body id="tasksList">
                    <BCardHeader class="border-0">
                        <BButton variant="primary" class="float-end" @click="createForm">Create</BButton>
                    </BCardHeader>
                    <BCardBody class="border border-dashed border-end-0 border-start-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </BCardBody>
                </BCard>
            </BCol>
        </BRow>

        <BModal v-model="modalForm" hide-footer class="modal-md">
            <h3>Create form</h3><hr>
            <div class="col-12">
                <form action="">
                    <div class="form-group">
                        <label for="name" class="form-label">Name<span class="text-danger"> *</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter the name">
                    </div>
                    <div class="form-group">
                        <label for="name" class="form-label">Name<span class="text-danger"> *</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter the name">
                    </div>
                    <div class="form-group">
                        <label for="name" class="form-label">Name<span class="text-danger"> *</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Enter the name">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success mt-4 w-100">Submit</button>
                    </div>

                </form>
            </div>
        </BModal>
    </Layout>
</template>
