<?php

declare(strict_types=1);

namespace Appocular\Differ\Http\Controllers;

use Appocular\Differ\Jobs\DiffRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Routing\Controller;

class DiffController extends Controller
{
    /**
     * Create new diff.
     */
    public function create(Request $request): Response
    {
        $this->validate($request, [
            'image_url' => 'required|string|min:1|max:255',
            'baseline_url' => 'required|string|min:1|max:255',
        ]);

        \dispatch(new DiffRequest($request->input('image_url'), $request->input('baseline_url')));

        // Always return success.
        return new Response();
    }
}
