<?php
class Notifications extends Controller {
    public function __construct(){
        if(!isLoggedIn()){
            // Allow access to logged-in users only
            redirect('users/login');
        }
        $this->notificationModel = $this->model('Notification');
    }

    public function index(){
        // This could be a page showing all notifications, but for now, we'll focus on the API
        redirect(''); // Redirect home or to a relevant page
    }

    // API endpoint to get unread notifications
    public function api_get_unread(){
        if(!isset($_SESSION['user_id'])){
            echo json_encode([]);
            return;
        }
        $notifications = $this->notificationModel->getUnreadNotifications($_SESSION['user_id']);
        header('Content-Type: application/json');
        echo json_encode($notifications);
    }

    // API endpoint to mark a notification as read
    public function api_mark_as_read($id){
        if($this->notificationModel->markAsRead($id)){
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}
?>