<?php require APPROOT . '/views/teachers/inc/header.php'; ?>
<?php flash('quiz_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Quizzes</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/teachers/add_quiz" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Quiz
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['quizzes'] as $quiz) : ?>
        <tr>
            <td><?php echo $quiz->title; ?></td>
            <td><?php echo $quiz->date; ?></td>
            <td><a href="<?php echo URLROOT; ?>/teachers/edit_quiz/<?php echo $quiz->id; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/teachers/delete_quiz/<?php echo $quiz->id; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/teachers/inc/footer.php'; ?>