@extends('layouts.backend')

@section('content')
<section class="section">
  <div class="section-header">
    <h1>Informasi Data Barang</h1>
    <div class="section-header-breadcrumb">
      @if (strtolower(Auth::user()->roles()->first()->slug) == 'admin')
      <div class="breadcrumb-item">
        <a href="{{ url('/products/create') }}">
          Tambah Barang
        </a>
      </div>
      @endif
    </div>
  </div>

  <div class="section-body">
    <h2 class="section-title">List Data Barang</h2>
    <div class="row">
      <div class="col-12 col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body p-1">
            
            <div class="row m-2">
              <div class="col-md-6">  
                <div class="form-group row">
                  <label for="searchName" class="col-md-4 m-2">Nama Barang</label>
                  <div class="col-md-7">
                    <input type="text" id="searchName" class="form-control" /> 
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group row">
                  <label for="searchSpesification" class="col-md-4 m-2">Spesifikasi</label>
                  <div class="col-md-7">
                    <input type="text" id="searchSpesification" class="form-control" /> 
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-group row">
                  <div class="col-md-7 offset-md-5 m-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="filterDatatable()">
                      <i class="fa fa-filter"></i> Filter
                    </button>

                    <button type="button" class="btn btn-success btn-sm" onclick="refreshDatatable()">
                      <i class="fa fa-spinner"></i> Refresh
                    </button>
                  </div>
                </div>

              </div>
            </div>

            {{-- DATA TABLE --}}
            <div class="row">&nbsp;</div>

            <div class="row">
              <div id="coverTableProduct" class="col-md-12 col-12 col-lg-12">
                @include('pages.products.datatable')
              </div>
            
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

  const urlPagination = "{!! url('/products') !!}"

  function setDatatable(page) {
    const searchName = $('#searchName').val();
    const searchSpesification = $('#searchSpesification').val();

    $.ajax({
      type: "GET",
      url: urlPagination + '?page=' + page + '&name=' + searchName + '&spesification=' + searchSpesification,
      success: function (data) {
        $("#coverTableProduct").html(data);
      }
    });
  }

  function filterDatatable() {
    var hrefPage = $(this).attr('href');
    var page = 1;

    console.log(hrefPage);

    if(hrefPage != '#' && hrefPage != '' && hrefPage != undefined) {
      page = parseInt($(this).attr('href').split('page=')[1]);
    }
    
    setDatatable(page);
  }

  function refreshDatatable() {
    $('#searchName').val('')
    $('#searchSpesification').val('')
    setDatatable(1);
  }

  $(function () {
    $(document).on('click', '.pagination a',function(event)
      {
        event.preventDefault();
        var hrefPage = $(this).attr('href');
        var page = 1;

        if(hrefPage != '#' && hrefPage != '') {
          page = parseInt($(this).attr('href').split('page=')[1]);
        }

        setDatatable(page)

      });
  });

</script>
@endpush