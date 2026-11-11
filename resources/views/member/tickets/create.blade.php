@extends('layouts.member')

@section('title', 'Create Ticket')

@section('sidebar')
    <a class="nav-link text-white" href="{{ route('member.dashboard') }}">
        <i class="bi bi-house-door me-2"></i>Dashboard
    </a>

    <a class="nav-link text-white active" href="{{ route('member.tickets.create') }}">
        <i class="bi bi-plus-circle me-2"></i>Create Ticket
    </a>

    <a class="nav-link text-white" href="{{ route('member.tickets.index') }}">
        <i class="bi bi-list-check me-2"></i>Ticket List
    </a>

    <a class="nav-link text-white" href="{{ route('member.stockouts.history') }}">
        <i class="bi bi-truck me-2"></i>Stockout History
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <h5 class="mb-0">Create Ticket</h5>
            </div>

            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('member.tickets.store') }}" method="POST" id="ticketCreateForm">
                    @csrf

                    <div class="row g-3">

                        {{-- COLUMN 1 --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0">Request</h6>
                                </div>

                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Requester</label>
                                        <input type="text" class="form-control"
                                            value="{{ auth()->user()->name }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Line Requester</label>
                                        <select name="line_requester" class="form-select" required>
                                            <option value="Spark Plug Insulator">
                                                Spark Plug Insulator
                                            </option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Partlist Name</label>

                                        <input type="text"
                                            name="partlist_name"
                                            id="partlistNameInput"
                                            class="form-control"
                                            list="partlistNames"
                                            autocomplete="off"
                                            required>

                                        <datalist id="partlistNames">
                                            @foreach ($partlists as $pl)
                                                <option value="{{ $pl->name }}"></option>
                                            @endforeach
                                        </datalist>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Qty</label>

                                        <input type="number"
                                            name="qty"
                                            id="qtyInput"
                                            class="form-control"
                                            min="1"
                                            onwheel="this.blur()"
                                            required>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- COLUMN 2 --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0">Partlist Info</h6>
                                </div>

                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Drawing No./Supplier</label>
                                        <input type="text" id="drawingNoSupplier"
                                            class="form-control" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Size/Type</label>
                                        <input type="text" id="sizeType"
                                            class="form-control" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Sub-Assy Drawing/Unit</label>
                                        <input type="text" id="subAssyDrawingUnit"
                                            class="form-control" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Location</label>
                                        <input type="text" id="locationField"
                                            class="form-control" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Available Stock</label>
                                        <input type="text"
                                            id="availableQty"
                                            class="form-control fw-bold"
                                            readonly>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- COLUMN 3 --}}
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-transparent">
                                    <h6 class="mb-0">Approval</h6>
                                </div>

                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Approver</label>

                                        <select name="approver_id" class="form-select" required>
                                            <option value="">-- Select Approver --</option>

                                            @foreach ($approvers as $approver)
                                                <option value="{{ $approver->id }}">
                                                    {{ $approver->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Remark</label>
                                        <textarea name="remark"
                                            class="form-control"
                                            rows="6"></textarea>
                                    </div>

                                    <div class="d-grid gap-2">

                                        <button type="submit"
                                            class="btn btn-primary"
                                            id="submitBtn">
                                            <i class="bi bi-send"></i>
                                            Send Approval
                                        </button>

                                        <a href="{{ route('member.tickets.index') }}"
                                            class="btn btn-secondary">
                                            <i class="bi bi-arrow-left"></i>
                                            Back
                                        </a>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('ticketCreateForm');
    const submitBtn = document.getElementById('submitBtn');

    const partlists = @json($partlistsJson);

    const partlistNameInput = document.getElementById('partlistNameInput');
    const qtyInput = document.getElementById('qtyInput');

    const drawingNoSupplier = document.getElementById('drawingNoSupplier');
    const sizeType = document.getElementById('sizeType');
    const subAssyDrawingUnit = document.getElementById('subAssyDrawingUnit');
    const locationField = document.getElementById('locationField');
    const availableQty = document.getElementById('availableQty');

    function clearFields() {
        drawingNoSupplier.value = '';
        sizeType.value = '';
        subAssyDrawingUnit.value = '';
        locationField.value = '';
        availableQty.value = '';
    }

    function fillPart(name) {

        const found = partlists.find(p => p.name === name);

        if (!found) {
            clearFields();
            return;
        }

        drawingNoSupplier.value = found.drawing_no_supplier ?? '';
        sizeType.value = found.size_type ?? '';
        subAssyDrawingUnit.value = found.sub_assy_drawing_unit ?? '';
        locationField.value = found.location ?? '';

        // FIX STOCK
        availableQty.value = found.qty ?? 0;
    }

    partlistNameInput.addEventListener('input', function () {
        fillPart(this.value);
    });

    form.addEventListener('submit', function (e) {

        const found = partlists.find(p => p.name === partlistNameInput.value);

        if (!found) {
            e.preventDefault();
            alert('Partlist tidak ditemukan.');
            return;
        }

        if (parseInt(qtyInput.value) > parseInt(found.qty)) {
            e.preventDefault();
            alert('Qty melebihi stock tersedia.');
            return;
        }

        if (!confirm('Kirim request ini?')) {
            e.preventDefault();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML =
            '<i class="bi bi-hourglass-split"></i> Processing...';
    });

    fillPart(partlistNameInput.value);

});
</script>
@endsection