<?php require APPROOT . '/views/teachers/inc/header.php'; ?>
<a href="<?php echo URLROOT; ?>/teachers/grades" class="btn btn-light"><i class="fa fa-backward"></i> Back</a>
<div class="card card-body bg-light mt-5">
    <h2>Edit Grade</h2>
    <p>Update a grade for a student</p>
    <form action="<?php echo URLROOT; ?>/teachers/edit_grade/<?php echo $data['id']; ?>" method="post">
        <div class="form-group">
            <label for="student_id">Student: <sup>*</sup></label>
            <select name="student_id" class="form-control form-control-lg <?php echo (!empty($data['student_id_err'])) ? 'is-invalid' : ''; ?>">
                <?php foreach($data['students'] as $student): ?>
                    <option value="<?php echo $student->studentId; ?>" <?php if($data['student_id'] == $student->studentId) echo 'selected'; ?>>
                        <?php echo $student->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="invalid-feedback"><?php echo $data['student_id_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="subject">Subject: <sup>*</sup></label>
            <input type="text" name="subject" class="form-control form-control-lg <?php echo (!empty($data['subject_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['subject']; ?>">
            <span class="invalid-feedback"><?php echo $data['subject_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="grade">Grade: <sup>*</sup></label>
            <input type="text" name="grade" class="form-control form-control-lg <?php echo (!empty($data['grade_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['grade']; ?>">
            <span class="invalid-feedback"><?php echo $data['grade_err']; ?></span>
        </div>
        <div class="form-group">
            <label for="date">Date: <sup>*</sup></label>
            <input type="date" name="date" class="form-control form-control-lg <?php echo (!empty($data['date_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['date']; ?>">
            <span class="invalid-feedback"><?php echo $data['date_err']; ?></span>
        </div>
        <input type="submit" class="btn btn-success" value="Submit">
    </form>
</div>
<?php require APPROOT . '/views/teachers/inc/footer.php'; ?>