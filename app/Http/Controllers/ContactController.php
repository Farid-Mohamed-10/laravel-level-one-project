<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request) {
        $data = $request->validated();

        Contact::create($data);

        return back()->with('contact-status', 'Your Message Sent Successfully');
    }
}
