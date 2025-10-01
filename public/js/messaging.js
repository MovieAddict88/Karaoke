document.addEventListener('DOMContentLoaded', function() {
    const messageContainer = document.getElementById('message-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const receiverId = document.getElementById('receiver-id').value;

    // Function to fetch messages
    const fetchMessages = async () => {
        try {
            const response = await fetch(`${urlRoot}/messages/api_get_messages/${withUserId}`);
            const messages = await response.json();

            // Clear container
            messageContainer.innerHTML = '';

            // Append new messages
            messages.forEach(message => {
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');

                if (message.sender_id == currentUserId) {
                    messageElement.classList.add('text-right', 'bg-light', 'p-2', 'mb-2', 'rounded');
                    messageElement.innerHTML = `<strong>You:</strong> ${message.message}`;
                } else {
                    messageElement.classList.add('text-left', 'bg-white', 'p-2', 'mb-2', 'border', 'rounded');
                    messageElement.innerHTML = `<strong>${message.sender_name || 'User'}:</strong> ${message.message}`;
                }
                messageContainer.appendChild(messageElement);
            });
            // Scroll to the bottom
            messageContainer.scrollTop = messageContainer.scrollHeight;
        } catch (error) {
            console.error('Error fetching messages:', error);
        }
    };

    // Function to send a message
    const sendMessage = async (e) => {
        e.preventDefault();
        const message = messageInput.value;
        if (!message) return;

        try {
            const response = await fetch(`${urlRoot}/messages/api_send_message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    receiver_id: receiverId,
                    message: message
                })
            });
            const result = await response.json();
            if (result.success) {
                messageInput.value = '';
                fetchMessages(); // Refresh messages immediately after sending
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    };

    // Event listeners
    if (messageForm) {
        messageForm.addEventListener('submit', sendMessage);
    }

    // Initial fetch and interval polling
    if(typeof withUserId !== 'undefined'){
        fetchMessages();
        setInterval(fetchMessages, 3000); // Poll every 3 seconds
    }
});