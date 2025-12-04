@extends('adminlte::page')

@section('title', 'Super Admin Dashboard')

@section('content_header')
    <h1>Super Admin Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $coolroomCount }}</h3>
                        <p>Coolroom</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-temperature-low"></i>
                    </div>
                    <a href="{{ route('superadmin.devices.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $freezerCount }}</h3>
                        <p>Freezer</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-snowflake"></i>
                    </div>
                    <a href="{{ route('superadmin.devices.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dataSuhuCount }}</h3>
                        <p>Data Suhu Entries</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <a href="{{ route('superadmin.data-suhu.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $userCount }}</h3>
                        <p>Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('superadmin.users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- /.row -->
        <!-- Main row -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Welcome to the Super Admin Dashboard</h3>
                    </div>
                    <div class="card-body">
                        <p>You have full access to manage users, devices, and view all temperature data.</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
@stop
