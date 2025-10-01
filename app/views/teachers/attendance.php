<?php require APPROOT . '/views/teachers/inc/header.php'; ?>
<?php flash('attendance_message'); ?>
<h1>Attendance</h1>
<form action="<?php echo URLROOT; ?>/teachers/attendance" method="post">
    <div class="form-group">
        <label for="attendance_date">Select Date:</label>
        <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo $data['attendance_date']; ?>" onchange="this.form.submit()">
    </div>
</form>

<form action="<?php echo URLROOT; ?>/teachers/attendance" method="post">
    <input type="hidden" name="attendance_date" value="<?php echo $data['attendance_date']; ?>">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['students'] as $student): ?>
                <tr>
                    <td><?php echo $student->name; ?></td>
                    <td>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="attendance[<?php echo $student->id; ?>]" id="present_<?php echo $student->id; ?>" value="present" <?php echo (isset($data['attendance'][$student->id]) && $data['attendance'][$student->id] == 'present') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="present_<?php echo $student->id; ?>">Present</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="attendance[<?php echo $student->id; ?>]" id="absent_<?php echo $student->id; ?>" value="absent" <?php echo (isset($data['attendance'][$student->id]) && $data['attendance'][$student->id] == 'absent') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="absent_<?php echo $student->id; ?>">Absent</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="attendance[<?php echo $student->id; ?>]" id="late_<?php echo $student->id; ?>" value="late" <?php echo (isset($data['attendance'][$student->id]) && $data['attendance'][$student->id] == 'late') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="late_<?php echo $student->id; ?>">Late</label>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="submit" value="Save Attendance" class="btn btn-success">
</form>
<?php require APPROOT . '/views/teachers/inc/footer.php'; ?>