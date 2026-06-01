<!-- Theme Settings Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center gap-2 px-3 py-3 offcanvas-header border-bottom border-dashed">
        <h5 class="flex-grow-1 mb-0">Theme Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body p-0 h-100" data-simplebar>

        <div class="p-3 border-bottom border-dashed">
            <h5 class="mb-3 fs-16 fw-bold">Color Scheme</h5>
            <div class="row">
                <div class="col-4">
                    <div class="form-check card-radio">
                        <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-light" value="light">
                        <label class="form-check-label p-3 w-100 d-flex justify-content-center align-items-center" for="layout-color-light">
                            <iconify-icon icon="solar:sun-bold-duotone" class="fs-32 text-muted"></iconify-icon>
                        </label>
                    </div>
                    <h5 class="fs-14 text-center text-muted mt-2">Light</h5>
                </div>
                <div class="col-4">
                    <div class="form-check card-radio">
                        <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-color-dark" value="dark">
                        <label class="form-check-label p-3 w-100 d-flex justify-content-center align-items-center" for="layout-color-dark">
                            <iconify-icon icon="solar:cloud-sun-2-bold-duotone" class="fs-32 text-muted"></iconify-icon>
                        </label>
                    </div>
                    <h5 class="fs-14 text-center text-muted mt-2">Dark</h5>
                </div>
            </div>
        </div>

        <div class="p-3 border-bottom border-dashed">
            <h5 class="mb-3 fs-16 fw-bold">Sidenav Size</h5>
            <div class="row">
                <div class="col-4">
                    <div class="form-check sidebar-setting card-radio">
                        <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-default" value="default">
                        <label class="form-check-label p-2 d-block text-center" for="sidenav-size-default">
                            <i class="ti ti-layout-sidebar-left-expand fs-24 d-block mb-1"></i>
                            <span class="fs-12">Default</span>
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-check sidebar-setting card-radio">
                        <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-compact" value="compact">
                        <label class="form-check-label p-2 d-block text-center" for="sidenav-size-compact">
                            <i class="ti ti-layout-sidebar fs-24 d-block mb-1"></i>
                            <span class="fs-12">Compact</span>
                        </label>
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-check sidebar-setting card-radio">
                        <input class="form-check-input" type="radio" name="data-sidenav-size" id="sidenav-size-sm-hover" value="sm-hover">
                        <label class="form-check-label p-2 d-block text-center" for="sidenav-size-sm-hover">
                            <i class="ti ti-layout-sidebar-right-expand fs-24 d-block mb-1"></i>
                            <span class="fs-12">Icon View</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
