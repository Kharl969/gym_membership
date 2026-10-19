<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="member_trainer_id">Trainer</label>
                        <select id="member_trainer_id" name="trainer_id" class="form-select" required <?= $trainers === [] ? 'disabled' : '' ?>>
                            <option value="" selected disabled>Select a trainer</option>
                            <?php foreach ($trainers as $trainer): ?>
                                <option value="<?= (int) $trainer['trainer_id'] ?>"><?= escape('#' . $trainer['trainer_id'] . ' - ' . $trainer['first_name'] . ' ' . $trainer['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($trainers === []): ?><div class="form-text">Add a trainer before registering a member.</div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="e.g. Juan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="e.g. Dela Cruz" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="e.g. 09123328752" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. juandelacruz@gmail.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Registered</label>
                        <input type="date" name="date_registered" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_member" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>