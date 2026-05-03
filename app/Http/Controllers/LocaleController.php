<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|in:en,de,fr,es,it'
        ]);

        Session::put('locale', $request->locale);
        App::setLocale($request->locale);

        return redirect()->back();
    }
}
