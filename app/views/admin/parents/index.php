<?php require APPROOT . '/views/admin/inc/header.php'; ?>
<?php flash('parent_message'); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h1>Parents</h1>
    </div>
    <div class="col-md-6">
        <a href="<?php echo URLROOT; ?>/admin/add_parent" class="btn btn-primary float-right">
            <i class="fa fa-pencil"></i> Add Parent
        </a>
    </div>
</div>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Parent Name</th>
            <th>Parent Email</th>
            <th>Student Name</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($data['parents'] as $parent) : ?>
        <tr>
            <td><?php echo $parent->parentName; ?></td>
            <td><?php echo $parent->parentEmail; ?></td>
            <td><?php echo $parent->studentName; ?></td>
            <td><a href="<?php echo URLROOT; ?>/admin/edit_parent/<?php echo $parent->parentId; ?>" class="btn btn-dark">Edit</a></td>
            <td>
                <form action="<?php echo URLROOT; ?>/admin/delete_parent/<?php echo $parent->parentId; ?>" method="post">
                    <input type="submit" value="Delete" class="btn btn-danger">
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require APPROOT . '/views/admin/inc/footer.php'; ?>