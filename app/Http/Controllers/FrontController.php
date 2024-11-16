<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Workshop;
use Illuminate\Http\Request;
use App\Service\FrontService;
use App\Http\Controllers\Controller;

class FrontController extends Controller
{
    protected $frontService;

    public function __construct(FrontService $frontService)
    {
        $this->frontService = $frontService;
    }
    public function index() {
        $data = $this->frontService->getFrontPageData();
        return view('front.index', $data);
    }

    public function details(Workshop $workshop) {
        return view('front.details', compact('workshop'));
    }

    public function category(Category $category) {
        return view('front.category', compact('category'));
    }
}
