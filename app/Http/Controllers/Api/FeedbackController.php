<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    /**
     * Get all feedback (for admin)
     */
    public function index()
    {
        $feedback = Feedback::with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stars' => $item->stars,
                    'full_name' => $item->full_name,
                    'email' => $item->email,
                    'category' => $item->category,
                    'message' => $item->message,
                    'user_id' => $item->user_id,
                    'submitted_at' => $item->created_at->format('M d, Y H:i'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'feedback' => $feedback,
                'total_count' => $feedback->count(),
            ],
        ]);
    }

    /**
     * Submit feedback (public or authenticated)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stars' => 'required|integer|min:1|max:5',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'category' => 'required|string|max:255',
            'message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $feedback = Feedback::create([
            'user_id' => $request->user() ? $request->user()->id : null,
            'stars' => $request->stars,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'category' => $request->category,
            'message' => $request->message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Thank you for your feedback!',
            'data' => [
                'feedback' => [
                    'id' => $feedback->id,
                    'stars' => $feedback->stars,
                    'full_name' => $feedback->full_name,
                    'email' => $feedback->email,
                    'category' => $feedback->category,
                    'message' => $feedback->message,
                    'submitted_at' => $feedback->created_at->format('M d, Y H:i'),
                ],
            ],
        ], 201);
    }

    /**
     * Get feedback statistics
     */
    public function statistics()
    {
        $totalFeedback = Feedback::count();
        $averageRating = Feedback::avg('stars');
        
        $ratingBreakdown = [
            '5_stars' => Feedback::where('stars', 5)->count(),
            '4_stars' => Feedback::where('stars', 4)->count(),
            '3_stars' => Feedback::where('stars', 3)->count(),
            '2_stars' => Feedback::where('stars', 2)->count(),
            '1_star' => Feedback::where('stars', 1)->count(),
        ];

        $categoryBreakdown = Feedback::select('category')
            ->selectRaw('count(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_feedback' => $totalFeedback,
                'average_rating' => round($averageRating, 2),
                'rating_breakdown' => $ratingBreakdown,
                'category_breakdown' => $categoryBreakdown,
            ],
        ]);
    }

    /**
     * Get single feedback
     */
    public function show($id)
    {
        $feedback = Feedback::with('user')->find($id);

        if (!$feedback) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'feedback' => [
                    'id' => $feedback->id,
                    'stars' => $feedback->stars,
                    'full_name' => $feedback->full_name,
                    'email' => $feedback->email,
                    'category' => $feedback->category,
                    'message' => $feedback->message,
                    'user_id' => $feedback->user_id,
                    'submitted_at' => $feedback->created_at->format('M d, Y H:i'),
                ],
            ],
        ]);
    }

    /**
     * Delete feedback (admin only)
     */
    public function destroy($id)
    {
        $feedback = Feedback::find($id);

        if (!$feedback) {
            return response()->json([
                'status' => 'error',
                'message' => 'Feedback not found',
            ], 404);
        }

        $feedback->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback deleted successfully',
        ]);
    }
}
