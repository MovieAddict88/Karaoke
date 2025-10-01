<?php require APPROOT . '/views/inc/header.php'; ?>
<a href="<?php echo URLROOT; ?>/messages" class="btn btn-light"><i class="fa fa-backward"></i> Back to Conversations</a>
<div class="card card-body mt-3">
    <h2 class="card-title">Chat with <?php echo $data['with_user_name']; ?></h2>
    <div id="message-container" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
        <!-- Messages will be loaded here by AJAX -->
    </div>
    <form id="message-form">
        <div class="form-group">
            <input type="hidden" id="receiver-id" value="<?php echo $data['with_user_id']; ?>">
            <textarea id="message-input" class="form-control" placeholder="Type your message..."></textarea>
        </div>
        <button type="submit" class="btn btn-success">Send</button>
    </form>
</div>
<script>
    // Pass PHP data to JavaScript
    const withUserId = <?php echo $data['with_user_id']; ?>;
    const currentUserId = <?php echo $_SESSION['user_id']; ?>;
</script>
<?php require APPROOT . '/views/inc/footer.php'; ?>