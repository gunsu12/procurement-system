@extends('adminlte::page')

@section('title', 'Create Request')

@section('content_header')
    <h1>Create Procurement Request</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('procurement.store') }}" method="POST" id="createForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Nominal Manager Requirement</label>
                        <input type="number" name="manager_nominal" class="form-control" required>
                    </div>

                    <div class="form-group mb-3">
                        <label>Supporting Document</label>
                        <input type="file" name="document" class="form-control">
                        <small class="text-muted">Allowed types: pdf, doc, docx, xls, xlsx, jpg, png. Max: 10MB.</small>
                    </div>

                    <h4>Items</h4>
                    <table class="table table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Spec</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Budget Info</th>
                                <th><button type="button" class="btn btn-sm btn-success" id="addItem">+</button></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="items[0][name]" class="form-control" required></td>
                                <td><input type="text" name="items[0][specification]" class="form-control"></td>
                                <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                                <td><input type="text" name="items[0][unit]" class="form-control" required></td>
                                <td><input type="text" name="items[0][budget_info]" class="form-control"></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        let itemIndex = 1;
        $('#addItem').click(function() {
            let html = `<tr>
            <td><input type="text" name="items[${itemIndex}][name]" class="form-control" required></td>
            <td><input type="text" name="items[${itemIndex}][specification]" class="form-control"></td>
            <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control" required></td>
            <td><input type="text" name="items[0][unit]" class="form-control" required></td>
            <td><input type="text" name="items[${itemIndex}][budget_info]" class="form-control"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-row">x</button></td>
        </tr>`;
            $('#itemsTable tbody').append(html);
            itemIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });
    </script>
@stop
