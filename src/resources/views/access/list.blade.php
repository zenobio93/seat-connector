@extends('web::layouts.grids.3-9')

@section('title', trans('seat-connector::seat.access_management'))
@section('page_header', trans('seat-connector::seat.access_management'))

@section('left')

  @include('seat-connector::access.includes.sidebar')

@stop

@section('right')
    @if(empty(config('seat-connector.drivers', [])))
        <div class="callout callout-warning">
            <h4>No driver available!</h4>
            <p>In order to use this page, you need to install a seat-connector driver.</p>
        </div>
    @endif

    @include('seat-connector::access.includes.table')
@stop

@push('javascript')
  <script>
      $('#connector-filter-type').change(function() {
          var filter_type = $('#connector-filter-type').val();

          $.each(['connector-filter-users', 'connector-filter-roles', 'connector-filter-corporations', 'connector-filter-titles', 'connector-filter-alliances', 'connector-filter-squads'], function (key, value) {
              if (value === ('connector-filter-' + filter_type)) {
                  $(('#' + value)).prop('disabled', false);
              } else {
                  $(('#' + value)).prop('disabled', true);
              }
          });

          if (filter_type === 'titles')
              $('#connector-filter-corporations, #connector-filter-titles').prop('disabled', false);
      }).select2();

      $('#connector-filter-users').select2({
          ajax: {
              url: '{{ route('seatcore::fastlookup.users') }}',
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 3
      });

      $('#connector-filter-roles').select2({
          ajax: {
              url: '{{ route('seat-connector.api.roles') }}',
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 1
      });

      $('#connector-filter-corporations').select2({
          ajax: {
              url: '{{ route('seatcore::fastlookup.corporations') }}',
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 3
      });

      $('#connector-filter-titles').select2({
          ajax: {
              url: '{{ route('seat-connector.api.titles') }}',
              data: function (params) {
                  return {
                      q: params.term,
                      corporation_id: $('#connector-filter-corporations').val()
                  };
              },
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 1
      });

      $('#connector-filter-alliances').select2({
          ajax: {
              url: '{{ route('seatcore::fastlookup.alliances') }}',
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 3
      });

      $('#connector-filter-squads').select2({
          ajax: {
              url: '{{ route('seat-connector.api.squads') }}',
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 1
      });

      $('#connector-driver')
          .change(function () {
              window.LaravelDataTables["dataTableBuilder"].ajax.reload();
          })
          .select2();

      $('#connector-set').select2({
          ajax: {
              url: '{{ route('seat-connector.api.sets') }}',
              data: function (params) {
                  return {
                      q: params.term,
                      driver: $('#connector-driver').val()
                  };
              },
              dataType: 'json',
              cache: true
          },
          minimumInputLength: 1
      });

      $('#connector-table-filters li a').click(function() {
          $('#connector-table-filters a.active').removeClass('active');
          $(this).addClass('active');

          window.LaravelDataTables["dataTableBuilder"].ajax.reload();
      });
  </script>
  {!! $dataTable->scripts() !!}
@endpush