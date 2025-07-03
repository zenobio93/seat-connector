@extends('web::layouts.grids.12')

@section('title', trans('seat-connector::seat.user_mapping'))
@section('page_header', trans('seat-connector::seat.user_mapping'))

@section('full')
  @if(config('seat-connector.drivers', []) == [])
    <div class="callout callout-warning">
      <h4>No driver available!</h4>
      <p>In order to use this page, you need to install a seat-connector driver.</p>
    </div>
  @else
    <div class="card">
      <div class="card-header">
        <ul class="nav nav-pills ml-auto p-2" id="connector-table-filters">
          @foreach(config('seat-connector.drivers', []) as $metadata)
            <li class="nav-item">
              @if($loop->last)
                <a href="#" class="nav-link active" role="tab" data-toggle="pill" aria-expanded="true" data-filter="{{ $metadata['name'] }}">
                  {{ ucfirst($metadata['name']) }}
                </a>
              @else
                <a href="#" class="nav-link" role="tab" data-toggle="tab" aria-expanded="true" data-filter="{{ $metadata['name'] }}">
                  {{ ucfirst($metadata['name']) }}
                </a>
              @endif
            </li>
          @endforeach
        </ul>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="tab-pane active">
            {!! $dataTable->table(['class' => 'table table-striped table-hover']) !!}
          </div>
        </div>
      </div>
    </div>

    @include('seat-connector::users.partials.edit-modal')
  @endif
@stop

@push('javascript')
  {!! $dataTable->scripts() !!}

  <script>
    $('#connector-table-filters li a').click(function() {
      $('#connector-table-filters a.active').removeClass('active');
      $(this).addClass('active');

      window.LaravelDataTables["dataTableBuilder"].ajax.reload();
    });
  </script>
@endpush