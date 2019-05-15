<?php

namespace Appocular\Differ\Http\Controllers;

use Appocular\Differ\Jobs\DiffRequest;
use Illuminate\Http\Request;

class DiffController extends Controller
{
    /**
     * Create new diff.
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'image_kid' => 'required|string|min:1|max:255',
            'baseline_kid' => 'required|string|min:1|max:255',
        ]);

        dispatch(new DiffRequest($request->input('image_kid'), $request->input('baseline_kid')));
    }
}
