<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markAsRead(){
        auth()->user()->unreadNotifications->markAsRead();
        $notification = notify('Notifications marked as read');
        return back()->with($notification);
    }

    public function read(){
        auth()->user()->unreadNotifications->markAsRead();
        $notification = notify('Notification marked as read');
        return back()->with($notification);
    }

    /**
     * Return unread notifications as JSON for AJAX polling
     */
    public function unread(Request $request)
    {
        $user = auth()->user();
        // Return the latest notifications (both read and unread) so the panel retains read items.
        // Keep the unread count separate for badge display.
        $notifications = $user->notifications()->latest()->limit(10)->get()->map(function($n){
            return [
                'id' => $n->id,
                'data' => $n->data,
                'created_at' => $n->created_at->diffForHumans(),
                'read' => $n->read_at ? true : false,
            ];
        });

        return response()->json([
            'success' => true,
            // unread count still reflects number of unread notifications
            'count' => $user->unreadNotifications->count(),
            'data' => $notifications,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        auth()->user()->notifications()->delete();
        $notification = notify('Notification has been deleted');
        return back()->with($notification);
    }
}
