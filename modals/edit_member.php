<?php
/** @var array<int, array<string, mixed>> $members Provided by dashboard/index.php. */
foreach ($members as $row): ?>
<div class="modal fade" id="editMemberModal<?= (int) $row['member_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <div class="modal-body">
                    <input type="hidden" name="member_id" value="<?= (int) $row['member_id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Trainer</label>
                        <select name="trainer_id" class="form-select" required>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?= (int) $trainer['trainer_id'] ?>" <?= (int) $trainer['trainer_id'] === (int) $row['trainer_id'] ? 'selected' : '' ?>><?= escape('#' . $trainer['trainer_id'] . ' - ' . $trainer['first_name'] . ' ' . $trainer['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= escape((string) $row['first_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= escape((string) $row['last_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" value="<?= escape((string) $row['contact_number']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= escape((string) $row['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Registered</label>
                        <input type="date" name="date_registered" class="form-control" value="<?= escape((string) $row['date_registered']) ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_member" class="btn btn-warning">Update Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>