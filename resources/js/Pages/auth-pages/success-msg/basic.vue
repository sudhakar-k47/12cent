<script>
import { Link, Head } from '@inertiajs/vue3';
export default {
  components: {
    Link, Head
  }
};
</script>

<template>
  <Head title="Success Message" />

  <div class="auth-page-wrapper pt-5">
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
      <div class="bg-overlay"></div>
      <div class="shape">
        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
          <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
        </svg>
      </div>
    </div>

    <div class="auth-page-content">
      <BContainer>
        <BRow>
          <BCol lg="12">
            <div class="text-center mt-sm-5 mb-4 text-white-50">
              <div>
                <Link href="/" class="d-inline-block auth-logo">
                <img src="@assets/images/logo-light.png" alt="" height="20" />
                </Link>
              </div>
              <p class="mt-3 fs-15 fw-medium">
                Premium Admin & Dashboard Template
              </p>
            </div>
          </BCol>
        </BRow>

        <BRow class="justify-content-center">
          <BCol md="8" lg="6" xl="5">
            <BCard no-body class="mt-4">
              <BCardBody class="p-4 text-center">
                <div class="avatar-lg mx-auto mt-2">
                  <div class="avatar-title bg-light text-success display-3 rounded-circle">
                    <i class="ri-checkbox-circle-fill"></i>
                  </div>
                </div>
                <div class="mt-4 pt-2">
                  <h4>Well done !</h4>
                  <p class="text-muted mx-4">
                    Aww yeah, you successfully read this important message.
                  </p>
                  <div class="mt-4">
                    <Link href="/auth/signin-basic" class="btn btn-success w-100">Back to Dashboard
                    </Link>
                  </div>
                </div>
              </BCardBody>
            </BCard>
          </BCol>
        </BRow>
      </BContainer>
    </div>

    <footer class="footer">
      <BContainer>
        <BRow>
          <BCol lg="12">
            <div class="text-center">
              <p class="mb-0 text-muted">
                &copy; {{ new Date().getFullYear() }} Velzon. Crafted with
                <i class="mdi mdi-heart text-danger"></i> by Themesbrand
              </p>
            </div>
          </BCol>
        </BRow>
      </BContainer>
    </footer>
  </div>
</template>