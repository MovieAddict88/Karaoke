<?php require APPROOT . '/views/teachers/inc/header.php'; ?>
<?php flash('grade_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Grades</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/teachers/add_grade" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Grade
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Subject</th>
            <th>Grade</th>
            <th>Date</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['grades'] as $grade) : ?>
        <tr>
            <td><?php echo $grade->studentName; ?></td>
            <td><?php echo $grade->subject; ?></td>
            <td><?php echo $grade->grade; ?></td>
            <td><?php echo $grade->date; ?></td>
            <td><a href="<?php echo URLROOT; ?>/teachers/edit_grade/<?php echo $grade->gradeId; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/teachers/delete_grade/<?php echo $grade->gradeId; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/teachers/inc/footer.php'; ?>