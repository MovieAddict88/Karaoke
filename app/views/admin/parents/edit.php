<?php require APPROOT . '/views/admin/inc/header.php'; ?>
<a href="<?php echo URLROOT; ?>/admin/parents" class="btn btn-light"><i class="fa fa-backward"></i> Back</a>
<div class="card card-body bg-light mt-5">
    <h2>Edit Parent</h2>
    <p>Update parent account</p>
    <form action="<?php echo URLROOT; ?>/admin/edit_parent/<?php echo $data['parent_id']; ?>" method="post">
        <input type="hidden" name="user_id" value="<?php echo $data['user_id']; ?>">
        <div class="form-group">
            <label for="name">Name: <sup>*</sup></label>
            <input type="text" name="name" class="form-control form-control-lg <?php echo (!empty($data['name_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['name']; ?>">
            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="email">Email: <sup>*</sup></label>
            <input type="email" name="email" class="form-control form-control-lg <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
            <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="student_id">Link to Student: <sup>*</sup></label>
            <select name="student_id" class="form-control form-control-lg">
                <?php foreach($data['students'] as $student): ?>
                    <option value="<?php echo $student->studentId; ?>" <?php if($data['student_id'] == $student->studentId) echo 'selected'; ?>>
                        <?php echo $student->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="submit" class="btn btn-success" value="Submit">
    </form>
</div>
<?php require APPROOT . '/views/admin/inc/footer.php'; ?>