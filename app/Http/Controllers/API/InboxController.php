<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MassageResource;
use App\Models\Inbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    // 👨‍🎓 Student: Get all received messages
    public function index()
    {
        $messages = Inbox::where('receiver_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return MassageResource::collection($messages);
    }

    // 👨‍🎓 Student: View single message
    public function show($id)
    {
        $message = Inbox::where('id', $id)
            ->where('receiver_id', Auth::id())
            ->first();

        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Mark as read
        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return new MassageResource($message);
    }

    // 👨‍🏫 Admin/Doctor: Send message
    public function store(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string',
        ]);

        $inbox = Inbox::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $data['receiver_id'],
            'message'     => $data['message'],
        ]);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MassageResource($inbox),
        ], 201);
    }
}
