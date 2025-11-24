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
                <a href="{{ route('superadmin.users.create') }}" class="btn btn-primary btn-sm">Add New User</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped">
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
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                            <td>
                                <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-info btn-sm">Edit</a>
                                <form action="{{ route('superadmin.users.destroy', $user->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm delete-user-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Handle delete button click with SweetAlert
            $('table').on('click', '.delete-user-btn', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                const row = $(this).closest('tr');
                const userName = row.find('td:nth-child(2)').text(); // Assuming name is the second td
                const userEmail = row.find('td:nth-child(3)').text(); // Assuming email is the third td

                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete the user:<br>
                           <strong>Name:</strong> ${userName}<br>
                           <strong>Email:</strong> ${userEmail}`,
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
