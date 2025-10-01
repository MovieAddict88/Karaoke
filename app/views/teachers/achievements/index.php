<?php require APPROOT . '/views/teachers/inc/header.php'; ?>
<?php flash('achievement_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Achievements</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/teachers/add_achievement" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Achievement
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Achievement</th>
            <th>Date</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['achievements'] as $achievement) : ?>
        <tr>
            <td><?php echo $achievement->studentName; ?></td>
            <td><?php echo $achievement->achievement; ?></td>
            <td><?php echo $achievement->date; ?></td>
            <td><a href="<?php echo URLROOT; ?>/teachers/edit_achievement/<?php echo $achievement->achievementId; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/teachers/delete_achievement/<?php echo $achievement->achievementId; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/teachers/inc/footer.php'; ?>