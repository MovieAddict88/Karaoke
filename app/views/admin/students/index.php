<?php require APPROOT . '/views/admin/inc/header.php'; ?>
<?php flash('student_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Students</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/admin/add_student" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Student
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>QR Code</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['students'] as $student) : ?>
        <tr>
            <td><?php echo $student->name; ?></td>
            <td><?php echo $student->email; ?></td>
            <td><?php echo $student->qr_code; ?></td>
            <td><a href="<?php echo URLROOT; ?>/admin/edit_student/<?php echo $student->studentId; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/admin/delete_student/<?php echo $student->studentId; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/admin/inc/footer.php'; ?>