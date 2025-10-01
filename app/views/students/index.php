<?php require APPROOT . '/views/students/inc/header.php'; ?>
<h1><?php echo $data['title']; ?></h1>
<p>Welcome, <?php echo $_SESSION['user_name']; ?>. Here is your academic overview.</p>

<div class="row">
    <div class="col-md-6">
        <h2>Your Grades</h2>
        <?php if(!empty($data['grades'])): ?>
            <ul class="list-group">
                <?php foreach($data['grades'] as $grade): ?>
                    <li class="list-group-item">
                        <strong><?php echo $grade->subject; ?>:</strong> <?php echo $grade->grade; ?>
                        <span class="text-muted float-right"><?php echo $grade->date; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No grades have been recorded for you yet.</p>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h2>Your Attendance</h2>
        <?php if(!empty($data['attendance'])): ?>
            <ul class="list-group">
                <?php foreach($data['attendance'] as $att): ?>
                    <li class="list-group-item">
                        <strong><?php echo $att->date; ?>:</strong>
                        <span class="badge badge-<?php
                            switch($att->status){
                                case 'present': echo 'success'; break;
                                case 'absent': echo 'danger'; break;
                                case 'late': echo 'warning'; break;
                            }
                        ?>">
                            <?php echo ucfirst($att->status); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No attendance has been recorded for you yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APPROOT . '/views/students/inc/footer.php'; ?>