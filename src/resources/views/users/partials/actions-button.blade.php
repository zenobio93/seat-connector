<div class="row">
  <button type="button" class="btn btn-sm btn-primary mr-1" data-toggle="modal" data-target="#userModal" data-name-override="{{ $row->name_override }}">
    Edit
  </button>
  <form method="post" action="{{ route('seat-connector.users.destroy', ['id' => $row->id]) }}">
    {!! csrf_field() !!}
    {!! method_field('DELETE') !!}
    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
  </form>
</div>