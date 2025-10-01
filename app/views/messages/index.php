<?php require APPROOT . '/views/inc/header.php'; ?>
<h1>Your Conversations</h1>
<div class="list-group">
    <?php foreach($data['conversations'] as $conversation): ?>
        <a href="<?php echo URLROOT; ?>/messages/chat/<?php echo $conversation->id; ?>" class="list-group-item list-group-item-action">
            <?php echo $conversation->name; ?> (<?php echo ucfirst($conversation->role); ?>)
        </a>
    <?php endforeach; ?>
</div>
<?php require APPROOT . '/views/inc/footer.php'; ?>