<?php
class Messages extends Controller {
    public function __construct(){
        if(!isLoggedIn()){
            redirect('users/login');
        }
        $this->messageModel = $this->model('Message');
        $this->userModel = $this->model('User');
    }

    public function index(){
        $conversations = $this->messageModel->getConversations($_SESSION['user_id']);
        $data = [
            'conversations' => $conversations
        ];
        $this->view('messages/index', $data);
    }

    public function chat($with_user_id){
        $messages = $this->messageModel->getMessages($_SESSION['user_id'], $with_user_id);
        // We need the other user's name for the chat window title
        $with_user = $this->userModel->getUserById($with_user_id);

        $data = [
            'messages' => $messages,
            'with_user_id' => $with_user_id,
            'with_user_name' => $with_user->name
        ];

        $this->view('messages/chat', $data);
    }

    // API endpoint to get messages
    public function api_get_messages($with_user_id){
        $messages = $this->messageModel->getMessages($_SESSION['user_id'], $with_user_id);
        header('Content-Type: application/json');
        echo json_encode($messages);
    }

    // API endpoint to send a message
    public function api_send_message(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $json = file_get_contents('php://input');
            $request = json_decode($json);

            $data = [
                'sender_id' => $_SESSION['user_id'],
                'receiver_id' => $request->receiver_id,
                'message' => $request->message
            ];

            if($this->messageModel->sendMessage($data)){
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
        }
    }
}
?>