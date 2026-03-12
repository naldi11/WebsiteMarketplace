<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Transaction $transaction)
    {
        if ($transaction->buyer_id !== auth()->id())
            abort(403);
        if ($transaction->status !== 'completed')
            abort(403);

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'photo' => 'nullable|image',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reviews', 'public');
        }

        foreach ($transaction->items as $item) {
            Review::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item->product_id,
                'reviewer_id' => auth()->id(),
                'rating' => $request->rating,
                'comment' => $request->comment,
                'photo' => $photoPath,
            ]);
        }

        return back()->with('success', 'Review terkirim!');
    }
}
