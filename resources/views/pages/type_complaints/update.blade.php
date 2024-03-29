@extends('layouts.backend')

@section('content')
<section class="section">
  <div class="section-header">
    <h1>Form Kategori Pengaduan</h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item">
        <a href="#">Kategori List</a>
      </div>
    </div>
  </div>

  <div class="section-body">
    <h2 class="section-title">Update Kategori Pengaduan</h2>
    <div class="row">
      <div class="col-lg-12 col-md-12 col-12">
        <div class="card">
          <div class="card-body p-5">
            
            <div class="col-lg-6 col-md-6">
              <form>
                @csrf
                <div class="form-group row">
                  <label for="title" class="col-sm-4 col-form-label">Judul</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control" name="title" id="title" value="{{ $record->title }}" />
                  </div>
                </div>
    
                <div class="form-group row">
                  <label for="role_id" class="col-sm-4 col-form-label">Jenis Operasional</label>
                  <div class="col-sm-8">
                    <select name="role_id" id="role_id" class="form-control select2">
                      @foreach ($roles as $r)
                          <option value="{{ $r->id }}" {{ $r->id === $record->role_id ? 'selected="selected"': '' }}>
                            {{ $r->name }}
                          </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-sm-8 offset-sm-4">
                    <button type="button" class="btn btn-info" onclick="update('{{ $record->id }}')">
                      <i class="fas fa-edit"></i> Update
                    </button>
                  </div>
                </div>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
  <script>


    function update(id){
      const url = "{!! url('/categories/complaint') !!}/" + id;
      const data = {
        title: $("#title").val(),
        role_id: $("#role_id").val(),
      }

      axios.put(url, data).then((response) => {
        const {data, status} = response;
        if(status == 200) {
          Swal.fire({
            title: 'SUCCESS',
            text: data.message,
            confirmButtonText: `OK`,
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "{!! url('/categories/complaint') !!}"
            }
          })
        }
      }).catch((error) => {
        console.log(error.response);
      });
    }

  </script>
@endpush