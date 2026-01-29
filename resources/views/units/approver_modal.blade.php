<!-- Approver Selection Modal -->
<div class="modal fade" id="approverModal" tabindex="-1" role="dialog" aria-labelledby="approverModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approverModalLabel">Select Approver</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" id="userSearch" class="form-control"
                        placeholder="Search by name, email, or role...">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Company</th>
                                <th width="80px">Action</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            @foreach($users as $user)
                                <tr class="user-row" data-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                    data-email="{{ $user->email }}" data-role="{{ $user->role }}"
                                    data-company="{{ $user->company->name ?? '' }}">
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                                    <td>{{ $user->company->name ?? '-' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary select-user"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}">
                                            <i class="fas fa-check"></i> Select
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('js')
    <script>
        $(document).ready(function () {
            // Approver Modal: Search functionality
            $('#userSearch').on('keyup', function () {
                const searchTerm = $(this).val().toLowerCase();

                $('.user-row').each(function () {
                    const name = $(this).data('name').toLowerCase();
                    const email = $(this).data('email').toLowerCase();
                    const role = $(this).data('role').toLowerCase();
                    const company = $(this).data('company').toLowerCase();

                    const matches = name.includes(searchTerm) ||
                        email.includes(searchTerm) ||
                        role.includes(searchTerm) ||
                        company.includes(searchTerm);

                    $(this).toggle(matches);
                });
            });

            // Approver Modal: Select user
            $('.select-user').on('click', function () {
                const userId = $(this).data('id');
                const userName = $(this).data('name');

                $('#approval_by').val(userId);
                $('#approval_by_display').val(userName);
                $('#approverModal').modal('hide');
            });

            // Clear approver selection
            $('#clearApprover').on('click', function () {
                $('#approval_by').val('');
                $('#approval_by_display').val('');
            });

            // Reset search when modal is closed
            $('#approverModal').on('hidden.bs.modal', function () {
                $('#userSearch').val('');
                $('.user-row').show();
            });
        });
    </script>
@endpush