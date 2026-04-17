<?php

namespace App\Http\Controllers;

class TutorialController extends Controller
{
    private array $topics = [
        'add-listing' => [
            'title' => 'How to add a new listing',
            'subtitle' => 'A simple step-by-step guide for adding your property.',
            'icon' => 'plus',
            'minutes' => 2,
        ],
        'edit-listing' => [
            'title' => 'How to edit a listing',
            'subtitle' => 'Update photos, price, or details on a listing you already added.',
            'icon' => 'pencil',
            'minutes' => 2,
        ],
        'migrate-listing' => [
            'title' => 'How to move a listing to another type',
            'subtitle' => 'Copy a listing into IPP, ICP, or Condo without re-typing everything.',
            'icon' => 'swap',
            'minutes' => 3,
        ],
    ];

    public function index()
    {
        $topics = $this->topics;

        return view('tutorials.index', compact('topics'));
    }

    public function show(string $topic)
    {
        abort_unless(array_key_exists($topic, $this->topics), 404);

        $current = $this->topics[$topic];
        $topics = $this->topics;
        $view = 'tutorials.' . $topic;

        return view($view, compact('current', 'topic', 'topics'));
    }
}
