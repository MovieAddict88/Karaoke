<?php require APPROOT . '/views/admin/inc/header.php'; ?>
<?php flash('teacher_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Teachers</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/admin/add_teacher" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Teacher
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['teachers'] as $teacher) : ?>
        <tr>
            <td><?php echo $teacher->name; ?></td>
            <td><?php echo $teacher->email; ?></td>
            <td><a href="<?php echo URLROOT; ?>/admin/edit_teacher/<?php echo $teacher->teacherId; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/admin/delete_teacher/<?php echo $teacher->teacherId; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/admin/inc/footer.php'; ?>