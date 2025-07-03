<div class="modal" tabindex="-1" id="userModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('seat-connector::seat.edit_user_mapping') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>{{ trans('seat-connector::seat.name_override') }}</label>
                    <input type="text" class="form-control mb-1" name="name-override" placeholder="{{ trans('seat-connector::seat.enter_custom_name') }}">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="name-override-enable" id="name-override-enable">
                        <label class="form-check-label" for="name-override-enable">
                            {{ trans('seat-connector::seat.enable_name_override') }}
                        </label>
                    </div>
                </div>
                <button type="button" class="btn btn-primary">{{ trans('seat-connector::seat.save') }}</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('seat-connector::seat.close') }}</button>
            </div>
        </div>
    </div>
</div>

@push('javascript')
    <script>
        $('#userModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget) // Button that triggered the modal
            const modal = $(this)

            const name_override = button.data('name-override')
            modal.find('.modal-body input[name="name-override"]').val(name_override)

            const name_override_enabled = name_override !== ""
            modal.find('.modal-body input[name="name-override-enable"]').prop( "checked", name_override_enabled)

        })
    </script>
@endpush