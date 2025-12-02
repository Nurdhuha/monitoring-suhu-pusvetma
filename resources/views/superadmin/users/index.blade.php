@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
    <h1>User Management</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Users</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createUserModal">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped" id="users-table">
                <thead>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th style="width: 150px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr data-id="{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td class="user-name">{{ $user->name }}</td>
                            <td class="user-email">{{ $user->email }}</td>
                            <td class="user-role">{{ $user->role }}</td>
                            <td>
                                <button class="btn btn-info btn-sm edit-user-btn" data-id="{{ $user->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-user-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create User Modal --}}
    <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="createUserForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="create_name">Name</label>
                            <input type="text" class="form-control" id="create_name" name="name" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="create_email">Email</label>
                            <input type="email" class="form-control" id="create_email" name="email" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="create_password">Password</label>
                            <input type="password" class="form-control" id="create_password" name="password" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="create_password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" id="create_password_confirmation" name="password_confirmation" required>
                        </div>
                        <div class="form-group">
                            <label for="create_role">Role</label>
                            <select class="form-control" id="create_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit User Modal --}}
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editUserForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_name">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_password">New Password (optional)</label>
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                        <div class="form-group">
                            <label for="edit_password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                        </div>
                        <div class="form-group">
                            <label for="edit_role">Role</label>
                            <select class="form-control" id="edit_role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="superadmin">Super Admin</option>
                            </select>
                            <span class="invalid-feedback" role="alert"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    function clearFormErrors(formId) {
        $(`${formId} .form-control`).removeClass('is-invalid');
        $(`${formId} .invalid-feedback`).text('');
    }

    function displayFormErrors(formId, errors) {
        for (const field in errors) {
            const input = $(`${formId} #${formId.includes('create') ? 'create' : 'edit'}_${field}`);
            input.addClass('is-invalid');
            input.next('.invalid-feedback').text(errors[field][0]);
        }
    }

    // Handle Create User form submission
    $('#createUserForm').on('submit', function(e) {
        e.preventDefault();
        clearFormErrors('#createUserForm');
        
        $.ajax({
            url: "{{ route('superadmin.users.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#createUserModal').modal('hide');
                Swal.fire('Success!', 'User created successfully.', 'success').then(() => {
                    location.reload(); // Easiest way to show the new user
                });
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayFormErrors('#createUserForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                }
            }
        });
    });

    // Handle Edit button click
    $('#users-table').on('click', '.edit-user-btn', function() {
        const userId = $(this).data('id');
        const url = "{{ route('superadmin.users.edit', ':id') }}".replace(':id', userId);

        clearFormErrors('#editUserForm');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(user) {
                $('#edit_user_id').val(user.id);
                $('#edit_name').val(user.name);
                $('#edit_email').val(user.email);
                $('#edit_role').val(user.role);
                $('#editUserModal').modal('show');
            },
            error: function() {
                Swal.fire('Error!', 'Could not fetch user data.', 'error');
            }
        });
    });

    // Handle Edit User form submission
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        clearFormErrors('#editUserForm');

        const userId = $('#edit_user_id').val();
        const url = "{{ route('superadmin.users.update', ':id') }}".replace(':id', userId);

        $.ajax({
            url: url,
            method: 'POST', // Using POST with _method='PUT'
            data: $(this).serialize(),
            success: function(response) {
                $('#editUserModal').modal('hide');
                Swal.fire('Success!', 'User updated successfully.', 'success');

                // Update the table row dynamically
                const row = $(`tr[data-id="${userId}"]`);
                row.find('.user-name').text($('#edit_name').val());
                row.find('.user-email').text($('#edit_email').val());
                row.find('.user-role').text($('#edit_role').val());
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    displayFormErrors('#editUserForm', xhr.responseJSON.errors);
                } else {
                    Swal.fire('Error!', 'An unexpected error occurred.', 'error');
                }
            }
        });
    });

    // Handle delete button click with SweetAlert
    $('#users-table').on('click', '.delete-user-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const userName = $(this).closest('tr').find('.user-name').text();

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete user: ${userName}. You won't be able to revert this!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@stop
