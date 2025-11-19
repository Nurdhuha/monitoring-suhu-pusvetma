@extends('adminlte::page')

@section('title', 'Add New Temperature Reading')

@section('content_header')
    <h1>Add New Temperature Reading</h1>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Temperature Reading Details</h3>
        </div>
        <form action="{{ route('data-suhu.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="device_id">Device</label>
                    <select class="form-control @error('device_id') is-invalid @enderror" id="device_id" name="device_id" required>
                        <option value="">-- Select a Device --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                                {{ $device->name }} ({{ $device->location }})
                            </option>
                        @endforeach
                    </select>
                    @error('device_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="section">Section</label>
                    <select class="form-control @error('section') is-invalid @enderror" id="section" name="section" required>
                        <option value="">-- Select a Section --</option>
                        <option value="pagi" {{ old('section') == 'pagi' ? 'selected' : '' }}>Pagi</option>
                        <option value="siang" {{ old('section') == 'siang' ? 'selected' : '' }}>Siang</option>
                        <option value="sore" {{ old('section') == 'sore' ? 'selected' : '' }}>Sore</option>
                    </select>
                    @error('section')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="temperature">Temperature (°C)</label>
                    <input type="number" step="0.01" class="form-control @error('temperature') is-invalid @enderror" id="temperature" name="temperature" placeholder="Enter temperature" value="{{ old('temperature') }}" required>
                    @error('temperature')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('data-suhu.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@stop
