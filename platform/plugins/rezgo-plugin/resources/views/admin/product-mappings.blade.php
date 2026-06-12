@extends('core/base::layouts/master')

@section('content')
<div class="container-xl">
    <div class="page-wrapper">
        <div class="container-xl">
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">{{ __('Product Mappings') }}</h2>
                    </div>
                    <div class="col-auto">
                        <div class="btn-group" role="group">
                            <a href="{{ route('rezgo.index') }}" class="btn btn-link">{{ __('Settings') }}</a>
                            <a href="{{ route('rezgo.submit-order.form') }}" class="btn btn-link">{{ __('Submit Order') }}</a>
                            <a href="{{ route('rezgo.submissions.index') }}" class="btn btn-link">{{ __('Submissions') }}</a>
                            <a href="{{ route('rezgo.logs.index') }}" class="btn btn-link">{{ __('Logs') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="container-xl">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <a class="btn-close" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <a class="btn-close" data-bs-dismiss="alert"></a>
                    </div>
                @endif

                <div class="row row-cards">
                    <div class="col-12">
                        <div class="card mb-3" id="bulk-action-bar" style="display:none;">
                            <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
                                <strong id="bulk-count">0</strong> mapping(s) selected
                                <form id="bulk-publish-form" action="{{ route('rezgo.product-mappings.bulk-publish') }}" method="POST" class="d-flex align-items-center gap-2 ms-3">
                                    @csrf
                                    <div id="bulk-hidden-ids"></div>
                                    <button type="submit" class="btn btn-success btn-sm"><i class="ti ti-check me-1"></i> Publish Selected Products</button>
                                </form>
                                <button type="button" class="btn btn-outline-secondary btn-sm ms-auto" id="deselect-all-mappings">Deselect All</button>
                            </div>
                        </div>
                    <div class="card">
                            <div class="table-responsive">
                                <table class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;"><input type="checkbox" class="form-check-input" id="check-all-mappings"></th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Rezgo Ticket Name') }}</th>
                                            <th>{{ __('Rezgo UID') }}</th>
                                            <th>{{ __('Passenger Type') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th style="width: 150px;">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mappings as $mapping)
                                            <tr>
                                                <td style="width:40px;">
                                                    <input type="checkbox" class="form-check-input mapping-check" value="{{ $mapping->id }}">
                                                </td>
                                                <td>
                                                    <strong>{{ $mapping->product->name ?? 'N/A' }}</strong>
                                                </td>
                                                <td style="word-break: break-word;">
                                                    <small>{{ $mapping->rezgo_title ?? '—' }}</small>
                                                </td>
                                                <td>
                                                    <code class="small">{{ $mapping->rezgo_uid ?? '—' }}</code>
                                                </td>
                                                <td>
                                                    {{ ucfirst($mapping->passenger_type) }}
                                                </td>
                                                <td>
                                                    {{ $mapping->is_active ? __('Active') : __('Inactive') }}
                                                </td>
                                                <td>
                                                    <a
                                                        class="btn btn-icon btn-primary edit-mapping-btn"
                                                        href="#"
                                                        data-mapping-id="{{ $mapping->id }}"
                                                        data-product-id="{{ $mapping->product_id }}"
                                                        data-rezgo-uid="{{ $mapping->rezgo_uid }}"
                                                        data-rezgo-title="{{ $mapping->rezgo_title }}"
                                                        data-passenger-type="{{ $mapping->passenger_type }}"
                                                    >
                                                        <x-core::icon name="ti ti-pencil" />
                                                    </a>
                                                    <form action="{{ route('rezgo.product-mappings.delete', $mapping->id) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="btn btn-icon btn-danger"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Delete mapping') }}"
                                                            onclick="return confirm('{{ __('Are you sure?') }}')"
                                                        >
                                                            <x-core::icon name="ti ti-trash" />
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    {{ __('No product mappings configured') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if ($mappings->hasPages())
                                <div class="card-footer d-flex align-items-center">
                                    {{ $mappings->links() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Available Rezgo Inventory -->
                    @if ($rezgoTours)
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title">{{ __('Available Rezgo Inventory') }}</h3>
                                    @if($totalInventoryCount > 0)
                                        <span class="badge bg-info text-white">{{ $totalInventoryCount }} total items</span>
                                    @endif
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Inventory Name') }}</th>
                                                <th>{{ __('UID') }}</th>
                                                <th>{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($rezgoTours as $tour)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $tour['name'] ?? $tour['item'] ?? 'N/A' }}</strong>
                                                        @if (isset($tour['option']) && $tour['option'])
                                                            <br><small class="text-muted">{{ $tour['option'] }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <code>{{ $tour['uid'] ?? '—' }}</code>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary map-tour-btn"
                                                            data-uid="{{ $tour['uid'] ?? '' }}"
                                                            data-title="{{ $tour['name'] ?? $tour['item'] ?? '' }}"
                                                            data-option="{{ $tour['option'] ?? '' }}">
                                                            {{ __('Map Product') }}
                                                        </button>
                                                        <form action="{{ route('rezgo.import-as-draft') }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            <input type="hidden" name="rezgo_uid" value="{{ $tour['uid'] ?? '' }}">
                                                            <input type="hidden" name="rezgo_title" value="{{ $tour['name'] ?? $tour['item'] ?? '' }}">
                                                            <button type="submit" class="btn btn-sm btn-success">{{ __('Import as Draft') }}</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @php
                                    $totalPages = ceil($totalInventoryCount / $perPage);
                                @endphp
                                @if($totalPages > 1)
                                    <div class="card-footer d-flex flex-column align-items-center">
                                        <p class="text-muted small mb-3">Showing {{ count($rezgoTours) }} of {{ $totalInventoryCount }} items (Page {{ $currentPage }} of {{ $totalPages }})</p>
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination pagination-sm">
                                                @if($currentPage > 1)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ route('rezgo.product-mappings.index', ['rezgo_page' => $currentPage - 1]) }}">&laquo; Previous</a>
                                                    </li>
                                                @endif
                                                
                                                @for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                                                    <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ route('rezgo.product-mappings.index', ['rezgo_page' => $i]) }}">{{ $i }}</a>
                                                    </li>
                                                @endfor
                                                
                                                @if($currentPage < $totalPages)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ route('rezgo.product-mappings.index', ['rezgo_page' => $currentPage + 1]) }}">Next &raquo;</a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </nav>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div class="modal modal-blur fade" id="mapModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Map Product to Rezgo Inventory') }}</h5>
            </div>
            <form action="{{ route('rezgo.product-mappings.save') }}" method="POST">
                @csrf
                <input type="hidden" id="mappingId" name="mapping_id" value="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Product') }}</label>
                        <select class="form-control" id="productSelect" name="product_id" required>
                            <option value="">{{ __('Select a product') }}</option>
                            @forelse ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @empty
                                <option disabled>{{ __('No products available') }}</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Rezgo Inventory UID') }}</label>
                        <input type="text" class="form-control" name="rezgo_uid" id="rezgoUid">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Rezgo Inventory Title') }}</label>
                        <input type="text" class="form-control" name="rezgo_title" id="rezgoTitle">
                        <small class="text-muted" id="rezgoTitleHint" style="display:none;">Title is set from Rezgo and cannot be changed here.</small>
                    </div>

                    <input type="hidden" id="rezgoOption" name="rezgo_option">

                    <div class="mb-3">
                        <label class="form-label">{{ __('Passenger Type') }}</label>
                        <select class="form-control" id="passengerType" name="passenger_type" required>
                            <option value="adult">{{ __('Adult') }}</option>
                            <option value="child">{{ __('Child') }}</option>
                            <option value="senior">{{ __('Senior') }}</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-link" data-bs-dismiss="modal">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save Mapping') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openMapModal() {
    var el = document.getElementById('mapModal');
    if (el) (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
}
function setTourData(uid, title, option = '') {
    document.getElementById('mappingId').value = '';
    document.getElementById('productSelect').value = '';
    var uidField = document.getElementById('rezgoUid');
    var titleField = document.getElementById('rezgoTitle');
    uidField.value = uid || '';
    uidField.setAttribute('readonly', 'readonly');
    titleField.value = (title || '') + (option ? ' — ' + option : '');
    titleField.setAttribute('readonly', 'readonly');
    document.getElementById('rezgoTitleHint').style.display = 'block';
    document.getElementById('rezgoOption').value = option || '';
    document.getElementById('passengerType').value = 'adult';
    openMapModal();
}

function setMappingData(mappingId, productId, uid, title, passengerType) {
    document.getElementById('mappingId').value = mappingId || '';
    document.getElementById('productSelect').value = productId || '';
    var uidField = document.getElementById('rezgoUid');
    var titleField = document.getElementById('rezgoTitle');
    uidField.value = uid || '';
    uidField.removeAttribute('readonly');
    titleField.value = title || '';
    titleField.removeAttribute('readonly');
    document.getElementById('rezgoTitleHint').style.display = 'none';
    document.getElementById('rezgoOption').value = '';
    document.getElementById('passengerType').value = passengerType || 'adult';
    openMapModal();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.map-tour-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setTourData(
                this.dataset.uid,
                this.dataset.title,
                this.dataset.option
            );
        });
    });
    document.querySelectorAll('.edit-mapping-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            setMappingData(
                this.dataset.mappingId,
                this.dataset.productId,
                this.dataset.rezgoUid,
                this.dataset.rezgoTitle,
                this.dataset.passengerType
            );
        });
    });
});

// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
    const mapModal = document.getElementById('mapModal');
    if (mapModal) {
        mapModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('mappingId').value = '';
            document.getElementById('rezgoUid').removeAttribute('readonly');
            document.getElementById('rezgoTitle').removeAttribute('readonly');
            document.getElementById('rezgoTitleHint').style.display = 'none';
        });
    }
});
</script>

<script>
(function(){
var checkAll=document.getElementById('check-all-mappings');
var bar=document.getElementById('bulk-action-bar');
var cnt=document.getElementById('bulk-count');
var ids=document.getElementById('bulk-hidden-ids');
function upd(){
  var ch=Array.from(document.querySelectorAll('.mapping-check:checked'));
  var al=document.querySelectorAll('.mapping-check');
  if(ch.length){bar.style.display='block';cnt.textContent=ch.length;ids.innerHTML='';ch.forEach(function(x){var i=document.createElement('input');i.type='hidden';i.name='mapping_ids[]';i.value=x.value;ids.appendChild(i);});}
  else{bar.style.display='none';}
  if(checkAll){checkAll.indeterminate=ch.length>0&&ch.length<al.length;checkAll.checked=al.length>0&&ch.length===al.length;}
}
if(checkAll)checkAll.addEventListener('change',function(){document.querySelectorAll('.mapping-check').forEach(function(x){x.checked=checkAll.checked;});upd();});
document.querySelectorAll('.mapping-check').forEach(function(x){x.addEventListener('change',upd);});
var db=document.getElementById('deselect-all-mappings');
if(db)db.addEventListener('click',function(){document.querySelectorAll('.mapping-check').forEach(function(x){x.checked=false;});upd();});
var csrf=(document.querySelector('meta[name=csrf-token]')||{}).content||'';
document.querySelectorAll('.rezgo-title-field').forEach(function(inp){
  function sv(){var fd=new FormData();fd.append('_token',csrf);fd.append('mapping_id',inp.dataset.mappingId);fd.append('rezgo_title',inp.value.trim());inp.style.borderColor='#aaa';fetch('{{ route("rezgo.product-mappings.update-title") }}',{method:'POST',body:fd}).then(function(r){inp.style.borderColor=r.ok?'#28a745':'#dc3545';setTimeout(function(){inp.style.borderColor='';},2000);}).catch(function(){inp.style.borderColor='#dc3545';});}
  inp.addEventListener('blur',sv);
  inp.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();sv();}});
});
})();
</script>

@endsection
