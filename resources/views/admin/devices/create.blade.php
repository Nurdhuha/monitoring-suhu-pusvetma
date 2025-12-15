@extends('adminlte::page')

@section('title', 'Add New Device')

@section('content_header')
    <h1>Add New Device</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Device Details</h3>
        </div>
        <form action="{{ route('admin.devices.store') }}" method="POST">
            @csrf
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label for="name">Device Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter device name" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label for="location">Location (Optional)</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Enter device location" value="{{ old('location') }}">
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('admin.devices.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop