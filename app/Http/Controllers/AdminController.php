<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\DataSuhu;

class AdminController extends Controller
{
    public function home()
    {
        $coolroomCount = Device::where('name', 'like', 'Coolroom%')->count();
        $freezerCount = Device::where('name', 'like', 'Freezer%')->count();
        $userCount = User::count();
        $dataSuhuCount = DataSuhu::count();

        return view('admin.home', compact('coolroomCount', 'freezerCount', 'userCount', 'dataSuhuCount'));
    }
}
